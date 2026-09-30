<?php

namespace App\Services;

use App\Enums\AiMatchStatus;
use App\Enums\ItemStatus;
use App\Models\AiMatch;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Notifications\NewPossibleMatch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MatchingService
{
    /**
     * Run matching for a lost or found item: find candidates, ask the AI
     * (or the fake matcher) to score them, and save qualifying matches.
     */
    public function run(LostItem|FoundItem $item): void
    {
        $candidates = $this->findCandidates($item);

        if ($candidates->isEmpty()) {
            return;
        }

        $rawMatches = config('matching.fake')
            ? $this->fakeMatches($item, $candidates)
            : $this->requestAiMatches($item, $candidates);

        if ($rawMatches === null) {
            return;
        }

        $this->saveMatches($item, $candidates, $rawMatches);
    }

    /**
     * Pre-filter candidates in MySQL: opposite report type, status open or
     * matched, same category (or "Others"), and date within 30 days. At
     * most `matching.max_candidates` results.
     */
    public function findCandidates(LostItem|FoundItem $item): Collection
    {
        $othersCategoryId = Category::where('name', 'Others')->value('id');
        $categoryIds = array_values(array_unique(array_filter([$item->category_id, $othersCategoryId])));
        $windowDays = config('matching.candidate_window_days');
        $limit = config('matching.max_candidates');

        if ($item instanceof LostItem) {
            $anchorDate = $item->date_lost;

            return FoundItem::query()
                ->whereIn('category_id', $categoryIds)
                ->whereIn('status', [ItemStatus::Open, ItemStatus::Matched])
                ->whereBetween('date_found', [$anchorDate->copy()->subDays($windowDays), $anchorDate->copy()->addDays($windowDays)])
                ->with('category')
                ->latest('date_found')
                ->take($limit)
                ->get();
        }

        $anchorDate = $item->date_found;

        return LostItem::query()
            ->whereIn('category_id', $categoryIds)
            ->whereIn('status', [ItemStatus::Open, ItemStatus::Matched])
            ->whereBetween('date_lost', [$anchorDate->copy()->subDays($windowDays), $anchorDate->copy()->addDays($windowDays)])
            ->with('category')
            ->latest('date_lost')
            ->take($limit)
            ->get();
    }

    /**
     * Build the prompt and request strict JSON output from OpenAI.
     * Returns null (and logs) on any failure, so the caller can bail out
     * without the item report itself ever failing to save.
     */
    protected function requestAiMatches(LostItem|FoundItem $item, Collection $candidates): ?array
    {
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            Log::warning('MatchingService: OPENAI_API_KEY is not set; skipping AI matching.', [
                'item_type' => $item::class,
                'item_id' => $item->id,
            ]);

            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model'),
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->buildPrompt($item, $candidates)],
                    ],
                ]);

            if ($response->failed()) {
                throw new RuntimeException('OpenAI request failed with status '.$response->status().': '.$response->body());
            }

            $content = $response->json('choices.0.message.content');
            $decoded = json_decode((string) $content, true);

            if (! is_array($decoded) || ! isset($decoded['matches']) || ! is_array($decoded['matches'])) {
                Log::warning('MatchingService: AI response was not valid JSON in the expected shape.', [
                    'item_type' => $item::class,
                    'item_id' => $item->id,
                    'raw' => $content,
                ]);

                return null;
            }

            return $decoded['matches'];
        } catch (\Throwable $e) {
            // Re-throw so the queued job fails and Laravel's queue retries it;
            // the lost/found item itself was already saved before this ran.
            Log::error('MatchingService: AI matching request failed.', [
                'item_type' => $item::class,
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
            You help match lost item reports with found item reports for a school
            lost-and-found system. Given one report and a list of candidate reports
            of the opposite type, decide which candidates plausibly describe the
            same physical item. Respond with strict JSON only, no prose, in this
            exact shape: {"matches": [{"candidate_id": 12, "score": 85, "reason": "short text"}]}.
            score is an integer from 0 to 100. Only include candidates you think
            are plausible matches (score 50 or higher). If none are plausible,
            return {"matches": []}.
            PROMPT;
    }

    protected function buildPrompt(LostItem|FoundItem $item, Collection $candidates): string
    {
        $reportLabel = $item instanceof LostItem ? 'LOST report' : 'FOUND report';
        $candidateLabel = $item instanceof LostItem ? 'FOUND' : 'LOST';

        $lines = [];
        $lines[] = "{$reportLabel}:";
        $lines[] = $this->describeItem($item);
        $lines[] = '';
        $lines[] = "Candidate {$candidateLabel} reports:";

        foreach ($candidates as $candidate) {
            $lines[] = "- candidate_id {$candidate->id}: ".$this->describeItem($candidate);
        }

        return implode("\n", $lines);
    }

    /**
     * Plain-text summary of an item for the AI prompt. Only item_name,
     * category, color, brand, location, date, and description are ever
     * included - never hidden_details, user names, emails, or phone numbers.
     */
    protected function describeItem(LostItem|FoundItem $item): string
    {
        $location = $item instanceof LostItem ? $item->location_lost : $item->location_found;
        $date = $item instanceof LostItem ? $item->date_lost : $item->date_found;

        $parts = [
            'name: '.$item->item_name,
            'category: '.$item->category->name,
            'color: '.($item->color ?: 'unknown'),
            'brand: '.($item->brand ?: 'unknown'),
            'location: '.$location,
            'date: '.$date->format('Y-m-d'),
            'description: '.$item->description,
        ];

        return implode(', ', $parts);
    }

    /**
     * Common English filler words excluded from fake-mode keyword overlap,
     * so scoring reflects meaningful words (item_name, color, brand,
     * description) rather than sentence glue like "with" or "the".
     */
    protected const STOPWORDS = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'from', 'had', 'has',
        'have', 'if', 'in', 'into', 'is', 'it', 'its', 'my', 'near', 'no', 'not', 'of', 'on',
        'or', 'such', 'that', 'the', 'their', 'then', 'there', 'these', 'they', 'this', 'to',
        'was', 'were', 'will', 'with',
    ];

    /**
     * Fake matcher used when config('matching.fake') is true: scores
     * candidates by simple keyword overlap so the whole matching flow can
     * be tested without spending API credits. Weights item_name and color
     * more heavily than the free-text description, since they're the more
     * reliable signals for whether two reports describe the same item.
     */
    protected function fakeMatches(LostItem|FoundItem $item, Collection $candidates): array
    {
        $itemNameWords = $this->significantWords($item->item_name);
        $itemDescriptionWords = $this->significantWords($item->description);

        $matches = [];

        foreach ($candidates as $candidate) {
            $nameScore = $this->overlapCoefficient($itemNameWords, $this->significantWords($candidate->item_name));
            $descriptionScore = $this->overlapCoefficient($itemDescriptionWords, $this->significantWords($candidate->description));
            $colorScore = ($item->color && $candidate->color && strtolower($item->color) === strtolower($candidate->color)) ? 1.0 : 0.0;

            $score = (int) round((0.5 * $nameScore + 0.2 * $colorScore + 0.3 * $descriptionScore) * 100);

            if ($score < 50) {
                continue;
            }

            $matches[] = [
                'candidate_id' => $candidate->id,
                'score' => $score,
                'reason' => 'Keyword overlap match (fake mode): similar name, color, or description.',
            ];
        }

        return $matches;
    }

    /**
     * Lowercased, stopword-filtered words from a piece of text.
     */
    protected function significantWords(?string $text): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower((string) $text), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_diff($words, self::STOPWORDS);

        return array_values(array_unique($words));
    }

    /**
     * Intersection size divided by the smaller set's size (0 if either is
     * empty) - more forgiving than Jaccard for short, unevenly-sized fields.
     */
    protected function overlapCoefficient(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        return count(array_intersect($a, $b)) / min(count($a), count($b));
    }

    /**
     * Validate the AI's response and save/update ai_matches. Discards
     * candidate ids not in the candidate list. Saves only score >= 50.
     * Re-running matching updates the existing row instead of duplicating
     * it, unless that row was already dismissed or confirmed.
     */
    protected function saveMatches(LostItem|FoundItem $item, Collection $candidates, array $rawMatches): void
    {
        $candidateIds = $candidates->pluck('id')->all();
        $minScore = config('matching.min_score_to_save');
        $minScoreToNotify = config('matching.min_score_to_notify');
        $modelUsed = config('matching.fake') ? 'fake' : config('services.openai.model');

        foreach ($rawMatches as $match) {
            if (! is_array($match) || ! in_array($match['candidate_id'] ?? null, $candidateIds, true)) {
                continue;
            }

            $score = (int) ($match['score'] ?? 0);

            if ($score < $minScore) {
                continue;
            }

            $score = min(100, max(0, $score));
            $reason = is_string($match['reason'] ?? null) ? mb_substr($match['reason'], 0, 500) : '';

            [$lostItemId, $foundItemId] = $item instanceof LostItem
                ? [$item->id, $match['candidate_id']]
                : [$match['candidate_id'], $item->id];

            $existing = AiMatch::where('lost_item_id', $lostItemId)
                ->where('found_item_id', $foundItemId)
                ->first();

            if ($existing && in_array($existing->status, [AiMatchStatus::Dismissed, AiMatchStatus::Confirmed], true)) {
                continue;
            }

            $isNew = ! $existing;

            $aiMatch = AiMatch::updateOrCreate(
                ['lost_item_id' => $lostItemId, 'found_item_id' => $foundItemId],
                [
                    'score' => $score,
                    'reason' => $reason,
                    'status' => AiMatchStatus::Suggested,
                    'model_used' => $modelUsed,
                ]
            );

            if ($isNew && $score >= $minScoreToNotify) {
                $this->notifyReporters($aiMatch);
            }
        }
    }

    protected function notifyReporters(AiMatch $aiMatch): void
    {
        $aiMatch->loadMissing(['lostItem.user', 'foundItem.user']);

        $aiMatch->lostItem->user->notify(new NewPossibleMatch($aiMatch));
        $aiMatch->foundItem->user->notify(new NewPossibleMatch($aiMatch));
    }
}

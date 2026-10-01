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
            $this->pruneStaleSuggestions($item, $candidates, []);

            return;
        }

        $rawMatches = config('matching.fake')
            ? $this->fakeMatches($item, $candidates)
            : $this->requestAiMatches($item, $candidates);

        // On an AI failure, keep existing suggestions untouched - we have no
        // new information about them.
        if ($rawMatches === null) {
            return;
        }

        $keptIds = $this->saveMatches($item, $candidates, $rawMatches);
        $this->pruneStaleSuggestions($item, $candidates, $keptIds);
    }

    /**
     * Remove "suggested" matches for this item that the latest run no longer
     * supports (e.g. the report was edited and the old match doesn't fit).
     * Only touches counterparts this run actually evaluated - or every
     * counterpart, when the candidate list wasn't capped, since then anything
     * missing from it no longer passes the pre-filter at all. Dismissed and
     * confirmed matches are human decisions and are never removed.
     */
    protected function pruneStaleSuggestions(LostItem|FoundItem $item, Collection $candidates, array $keptIds): void
    {
        [$ownKey, $otherKey] = $item instanceof LostItem
            ? ['lost_item_id', 'found_item_id']
            : ['found_item_id', 'lost_item_id'];

        $query = AiMatch::where($ownKey, $item->id)
            ->where('status', AiMatchStatus::Suggested)
            ->whereNotIn($otherKey, $keptIds);

        $candidateListWasCapped = $candidates->count() >= config('matching.max_candidates');

        if ($candidateListWasCapped) {
            $query->whereIn($otherKey, $candidates->pluck('id'));
        }

        $query->delete();
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
            $baseUrl = rtrim(config('services.openai.base_url'), '/');

            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => config('services.openai.model'),
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->buildPrompt($item, $candidates)],
                    ],
                ]);

            if ($response->failed()) {
                throw new RuntimeException('AI matching request failed with status '.$response->status().': '.$response->body());
            }

            $content = $response->json('choices.0.message.content');
            $decoded = $this->extractJsonMatches($content);

            if ($decoded === null) {
                Log::warning('MatchingService: AI response was not valid JSON in the expected shape.', [
                    'item_type' => $item::class,
                    'item_id' => $item->id,
                    'raw' => $content,
                ]);

                return null;
            }

            return $decoded;
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

    /**
     * Parse the AI's response into a matches array. Smaller local models
     * (e.g. via Ollama) don't always follow "JSON only" as strictly as
     * GPT-family models - they sometimes wrap the JSON in a markdown code
     * fence or add a stray sentence before/after it - so this tries a
     * direct decode first, then falls back to extracting the first
     * {...} block before giving up.
     */
    protected function extractJsonMatches(?string $content): ?array
    {
        if (! $content) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            $stripped = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($content));
            $decoded = json_decode(trim($stripped), true);
        }

        if (! is_array($decoded) && preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
        }

        if (! is_array($decoded) || ! isset($decoded['matches']) || ! is_array($decoded['matches'])) {
            return null;
        }

        return $decoded['matches'];
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
            You help match lost item reports with found item reports for a school
            lost-and-found system. Given one report and a list of candidate reports
            of the opposite type, decide which candidates plausibly describe the
            same physical item. Respond with strict JSON only, no prose, in this
            exact shape: {"matches": [{"candidate_id": 12, "score": 85, "reason": "short text"}]}.

            Rules for scoring:
            1. First check the KIND of object. A wallet is not a water bottle, keys
               are not a jacket. If the two reports describe different kinds of
               objects, it is NOT a match - leave it out entirely, no matter how
               close the location or date is.
            2. Only for the same kind of object, score how well the details agree:
               - 85-100: same kind of object AND distinctive details agree
                 (color, brand, marks, contents).
               - 65-84: same kind of object, color agrees, no conflicting details.
               - 50-64: same kind of object but details are vague or partly differ.
            3. Location and date are weak supporting evidence only. Never use
               location or date as the main reason for a match.
            4. A clear conflict (different color, different brand) means leave it out.
               A detail that is missing or "unknown" on one side is NOT a conflict.

            The "reason" must name the matching details in a few words, e.g.
            "both black leather wallets with a red logo".
            score is an integer from 0 to 100. Only include candidates scoring 50
            or higher. If none qualify, return {"matches": []}.
            Output ONLY the JSON object above. Do not wrap it in a markdown code
            fence, do not add any explanation before or after it, and do not
            repeat these instructions.
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
    protected function saveMatches(LostItem|FoundItem $item, Collection $candidates, array $rawMatches): array
    {
        $candidateIds = $candidates->pluck('id')->all();
        $minScore = config('matching.min_score_to_save');
        $minScoreToNotify = config('matching.min_score_to_notify');
        $modelUsed = config('matching.fake') ? 'fake' : config('services.openai.model');
        $keptIds = [];

        foreach ($rawMatches as $match) {
            if (! is_array($match) || ! in_array($match['candidate_id'] ?? null, $candidateIds, true)) {
                continue;
            }

            $score = (int) ($match['score'] ?? 0);

            if ($score < $minScore) {
                continue;
            }

            // The AI judges similarity, but small models sometimes claim colors
            // agree when they plainly don't - so enforce that hard fact in code.
            if ($this->colorsConflict($item->color, $candidates->firstWhere('id', $match['candidate_id'])->color)) {
                continue;
            }

            $score = min(100, max(0, $score));
            $reason = is_string($match['reason'] ?? null) ? mb_substr($match['reason'], 0, 500) : '';
            $keptIds[] = $match['candidate_id'];

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

        return $keptIds;
    }

    /**
     * Two reported colors conflict when both are filled in and share no
     * word ("black" vs "dark black" agree; "blue" vs "green" conflict).
     * A missing color on either side is never a conflict.
     */
    protected function colorsConflict(?string $a, ?string $b): bool
    {
        $aWords = $this->significantWords($a);
        $bWords = $this->significantWords($b);

        if (empty($aWords) || empty($bWords)) {
            return false;
        }

        return empty(array_intersect($aWords, $bWords));
    }

    protected function notifyReporters(AiMatch $aiMatch): void
    {
        $aiMatch->loadMissing(['lostItem.user', 'foundItem.user']);

        $aiMatch->lostItem->user->notify(new NewPossibleMatch($aiMatch));
        $aiMatch->foundItem->user->notify(new NewPossibleMatch($aiMatch));
    }
}

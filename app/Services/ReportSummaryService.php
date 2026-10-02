<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Models\FoundItem;
use App\Models\LostItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds the numbers used by the public landing page, the admin dashboard,
 * and the admin "Reports & export" page, so every page calculates them the
 * same way.
 */
class ReportSummaryService
{
    /**
     * Recovery rate = found items that made it back to their owner, out of
     * all found items. Pass a date range to limit it to items found then.
     */
    public function recoveryRate(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        $total = $this->foundQuery($from, $to)->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->recoveredCount($from, $to) / $total * 100);
    }

    /**
     * How many found items were handed back to their owners.
     */
    public function recoveredCount(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        return $this->recovered($this->foundQuery($from, $to))->count();
    }

    /**
     * "Recovered" means the item was actually handed over: it is "returned",
     * or it has a completed claim (returned, then closed). A report that was
     * simply withdrawn is "closed" too, so status alone is not enough.
     */
    protected function recovered($foundQuery)
    {
        return $foundQuery->where(fn ($q) => $q
            ->where('status', ItemStatus::Returned)
            ->orWhereHas('claims', fn ($c) => $c->whereNotNull('completed_at')));
    }

    /**
     * Totals for a date range, for the admin export page.
     */
    public function summary(CarbonInterface $from, CarbonInterface $to): array
    {
        $lost = $this->lostQuery($from, $to);
        $found = $this->foundQuery($from, $to);

        return [
            'lost' => (clone $lost)->count(),
            'found' => (clone $found)->count(),
            'returned' => $this->recovered(clone $found)->count(),
            'open' => (clone $lost)->where('status', ItemStatus::Open)->count()
                + (clone $found)->where('status', ItemStatus::Open)->count(),
            'recoveryRate' => $this->recoveryRate($from, $to),
        ];
    }

    /**
     * Most common locations across lost and found reports, e.g. to show
     * where things get lost most on campus. Returns [location => count].
     */
    public function topLocations(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        $lost = $this->lostQuery($from, $to)->pluck('location_lost');
        $found = $this->foundQuery($from, $to)->pluck('location_found');

        return $this->countAndRank($lost->merge($found), $limit);
    }

    /**
     * Most common categories across lost and found reports. Returns [category => count].
     */
    public function topCategories(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        $lost = $this->lostQuery($from, $to)->with('category')->get()->pluck('category.name');
        $found = $this->foundQuery($from, $to)->with('category')->get()->pluck('category.name');

        return $this->countAndRank($lost->merge($found), $limit);
    }

    /** Column headings for the CSV export. */
    public const CSV_HEADINGS = [
        'Type', 'Tag No.', 'Item', 'Category', 'Color', 'Brand',
        'Location', 'Date', 'Status', 'Reported by', 'Reported on',
    ];

    /**
     * One row per lost and found report in the period, for the CSV export.
     *
     * Privacy: hidden_details, emails, and phone numbers are never included.
     * Uses lazy() so a large export doesn't load every row into memory at once.
     */
    public function csvRows(CarbonInterface $from, CarbonInterface $to): \Generator
    {
        $reports = [
            ['Lost', 'L-', $this->lostQuery($from, $to), 'location_lost', 'date_lost'],
            ['Found', 'F-', $this->foundQuery($from, $to), 'location_found', 'date_found'],
        ];

        foreach ($reports as [$type, $prefix, $query, $locationColumn, $dateColumn]) {
            foreach ($query->with(['category', 'user'])->orderBy($dateColumn)->orderBy('id')->lazy() as $item) {
                yield array_map([$this, 'csvSafe'], [
                    $type,
                    $prefix.str_pad($item->id, 4, '0', STR_PAD_LEFT),
                    $item->item_name,
                    $item->category->name,
                    $item->color,
                    $item->brand,
                    $item->{$locationColumn},
                    $item->{$dateColumn}->format('Y-m-d'),
                    $item->status->label(),
                    $item->user->name,
                    $item->created_at->format('Y-m-d H:i'),
                ]);
            }
        }
    }

    /**
     * Stops "CSV injection": a cell starting with = + - or @ could run as a
     * formula when the file is opened in Excel, so we prefix it with a quote.
     */
    protected function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /**
     * Counts identical values, ignoring letter case and extra spaces, so
     * "Library" and "library " are treated as the same place.
     */
    protected function countAndRank(Collection $values, int $limit): Collection
    {
        return $values
            ->filter()
            ->groupBy(fn ($value) => mb_strtolower(trim($value)))
            ->map(fn ($group) => ['label' => trim($group->first()), 'count' => $group->count()])
            ->sortByDesc('count')
            ->take($limit)
            ->mapWithKeys(fn ($row) => [$row['label'] => $row['count']]);
    }

    protected function lostQuery(?CarbonInterface $from, ?CarbonInterface $to)
    {
        return LostItem::query()
            ->when($from, fn ($q) => $q->whereDate('date_lost', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_lost', '<=', $to));
    }

    protected function foundQuery(?CarbonInterface $from, ?CarbonInterface $to)
    {
        return FoundItem::query()
            ->when($from, fn ($q) => $q->whereDate('date_found', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date_found', '<=', $to));
    }
}

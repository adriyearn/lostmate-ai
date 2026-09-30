<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'lost') === 'found' ? 'found' : 'lost';

        $filters = array_merge(
            ['q' => '', 'category_id' => '', 'location' => '', 'date_from' => '', 'date_to' => '', 'status' => '', 'sort' => 'newest'],
            array_filter($request->only(['q', 'category_id', 'location', 'date_from', 'date_to', 'status', 'sort']), fn ($v) => $v !== null)
        );
        $filters['sort'] = $filters['sort'] === 'oldest' ? 'oldest' : 'newest';

        if ($tab === 'found') {
            $items = $this->filteredFoundItems($request)->paginate(12)->withQueryString();
            $locations = FoundItem::query()->distinct()->orderBy('location_found')->pluck('location_found');
        } else {
            $items = $this->filteredLostItems($request)->paginate(12)->withQueryString();
            $locations = LostItem::query()->distinct()->orderBy('location_lost')->pluck('location_lost');
        }

        return view('browse.index', [
            'tab' => $tab,
            'items' => $items,
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => $locations,
            'statuses' => ItemStatus::cases(),
            'filters' => $filters,
        ]);
    }

    protected function filteredLostItems(Request $request): Builder
    {
        $query = LostItem::query()->with(['category', 'images']);

        $this->applyKeywordSearch($query, $request, 'location_lost');
        $this->applyCommonFilters($query, $request, 'date_lost');

        return $query;
    }

    protected function filteredFoundItems(Request $request): Builder
    {
        $query = FoundItem::query()->with(['category', 'images']);

        $this->applyKeywordSearch($query, $request, 'location_found');
        $this->applyCommonFilters($query, $request, 'date_found');

        return $query;
    }

    /**
     * Search item_name, description, color, brand, and location.
     * hidden_details is intentionally never included here.
     */
    protected function applyKeywordSearch(Builder $query, Request $request, string $locationColumn): void
    {
        $keyword = trim((string) $request->query('q'));

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $q) use ($keyword, $locationColumn) {
            $q->where('item_name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('color', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%")
                ->orWhere($locationColumn, 'like', "%{$keyword}%");
        });
    }

    protected function applyCommonFilters(Builder $query, Request $request, string $dateColumn): void
    {
        $query
            ->when($request->query('category_id'), fn (Builder $q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->query('location'), fn (Builder $q, $location) => $q->where($dateColumn === 'date_lost' ? 'location_lost' : 'location_found', $location))
            ->when($request->query('status'), fn (Builder $q, $status) => $q->where('status', $status))
            ->when($request->query('date_from'), fn (Builder $q, $date) => $q->whereDate($dateColumn, '>=', $date))
            ->when($request->query('date_to'), fn (Builder $q, $date) => $q->whereDate($dateColumn, '<=', $date))
            ->orderBy($dateColumn, $request->query('sort') === 'oldest' ? 'asc' : 'desc');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\AiMatchStatus;
use App\Enums\ItemStatus;
use App\Http\Requests\StoreFoundItemRequest;
use App\Http\Requests\UpdateFoundItemRequest;
use App\Jobs\RunItemMatching;
use App\Models\Category;
use App\Models\FoundItem;
use App\Services\ItemImageService;
use App\Services\ItemStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundItemController extends Controller
{
    public function __construct(protected ItemImageService $itemImages) {}

    public function create(): View
    {
        $this->authorize('create', FoundItem::class);

        return view('found-items.create', [
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreFoundItemRequest $request): RedirectResponse
    {
        $this->authorize('create', FoundItem::class);

        $foundItem = $request->user()->foundItems()->create($request->safe()->except('images'));

        $this->itemImages->store($foundItem, $request->file('images', []));

        return redirect()
            ->route('found-items.show', $foundItem)
            ->with('success', 'Your found item report has been submitted.');
    }

    public function show(FoundItem $foundItem): View
    {
        $this->authorize('view', $foundItem);

        $foundItem->load(['user.profile', 'category', 'images']);

        return view('found-items.show', ['foundItem' => $foundItem]);
    }

    public function edit(FoundItem $foundItem): View
    {
        $this->authorize('update', $foundItem);

        $foundItem->load('images');

        return view('found-items.edit', [
            'foundItem' => $foundItem,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateFoundItemRequest $request, FoundItem $foundItem): RedirectResponse
    {
        $foundItem->update($request->safe()->except(['images', 'remove_images']));

        foreach ($request->input('remove_images', []) as $imageId) {
            $image = $foundItem->images()->find($imageId);

            if ($image) {
                $this->itemImages->delete($image);
            }
        }

        $this->itemImages->store($foundItem, $request->file('images', []));

        return redirect()
            ->route('found-items.show', $foundItem)
            ->with('success', 'Your found item report has been updated.');
    }

    public function destroy(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->authorize('delete', $foundItem);

        $foundItem->delete();

        return redirect()
            ->route('my-reports.index')
            ->with('success', 'Your found item report has been deleted.');
    }

    public function matches(FoundItem $foundItem): View
    {
        $this->authorize('viewMatches', $foundItem);

        $matches = $foundItem->aiMatches()
            ->where('status', '!=', AiMatchStatus::Dismissed)
            ->has('lostItem')
            ->with(['lostItem.category', 'lostItem.images'])
            ->orderByDesc('score')
            ->get();

        return view('found-items.matches', [
            'foundItem' => $foundItem,
            'matches' => $matches,
        ]);
    }

    public function rerunMatching(FoundItem $foundItem): RedirectResponse
    {
        $this->authorize('rerunMatching', $foundItem);

        RunItemMatching::dispatch($foundItem);

        return back()->with('success', 'Matching has been queued to run again for this item.');
    }

    public function claims(FoundItem $foundItem): View
    {
        $this->authorize('update', $foundItem);

        $claims = $foundItem->claims()
            ->with(['claimant.profile', 'lostItem'])
            ->latest()
            ->get();

        return view('found-items.claims', [
            'foundItem' => $foundItem,
            'claims' => $claims,
        ]);
    }

    public function withdraw(FoundItem $foundItem, ItemStatusService $statusService): RedirectResponse
    {
        $this->authorize('update', $foundItem);

        $statusService->transition($foundItem, ItemStatus::Closed);

        return back()->with('success', 'This report has been withdrawn and closed.');
    }
}

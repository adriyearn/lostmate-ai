<?php

namespace App\Http\Controllers;

use App\Enums\AiMatchStatus;
use App\Http\Requests\StoreLostItemRequest;
use App\Http\Requests\UpdateLostItemRequest;
use App\Jobs\RunItemMatching;
use App\Models\Category;
use App\Models\LostItem;
use App\Services\ItemImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LostItemController extends Controller
{
    public function __construct(protected ItemImageService $itemImages) {}

    public function create(): View
    {
        $this->authorize('create', LostItem::class);

        return view('lost-items.create', [
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLostItemRequest $request): RedirectResponse
    {
        $this->authorize('create', LostItem::class);

        $lostItem = $request->user()->lostItems()->create($request->safe()->except('images'));

        $this->itemImages->store($lostItem, $request->file('images', []));

        return redirect()
            ->route('lost-items.show', $lostItem)
            ->with('success', 'Your lost item report has been submitted.');
    }

    public function show(LostItem $lostItem): View
    {
        $this->authorize('view', $lostItem);

        $lostItem->load(['user', 'category', 'images']);

        return view('lost-items.show', ['lostItem' => $lostItem]);
    }

    public function edit(LostItem $lostItem): View
    {
        $this->authorize('update', $lostItem);

        $lostItem->load('images');

        return view('lost-items.edit', [
            'lostItem' => $lostItem,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLostItemRequest $request, LostItem $lostItem): RedirectResponse
    {
        $lostItem->update($request->safe()->except(['images', 'remove_images']));

        foreach ($request->input('remove_images', []) as $imageId) {
            $image = $lostItem->images()->find($imageId);

            if ($image) {
                $this->itemImages->delete($image);
            }
        }

        $this->itemImages->store($lostItem, $request->file('images', []));

        return redirect()
            ->route('lost-items.show', $lostItem)
            ->with('success', 'Your lost item report has been updated.');
    }

    public function destroy(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->authorize('delete', $lostItem);

        $lostItem->delete();

        return redirect()
            ->route('my-reports.index')
            ->with('success', 'Your lost item report has been deleted.');
    }

    public function matches(LostItem $lostItem): View
    {
        $this->authorize('viewMatches', $lostItem);

        $matches = $lostItem->aiMatches()
            ->where('status', '!=', AiMatchStatus::Dismissed)
            ->with(['foundItem.category', 'foundItem.images'])
            ->orderByDesc('score')
            ->get();

        return view('lost-items.matches', [
            'lostItem' => $lostItem,
            'matches' => $matches,
        ]);
    }

    public function rerunMatching(LostItem $lostItem): RedirectResponse
    {
        $this->authorize('rerunMatching', $lostItem);

        RunItemMatching::dispatch($lostItem);

        return back()->with('success', 'Matching has been queued to run again for this item.');
    }
}

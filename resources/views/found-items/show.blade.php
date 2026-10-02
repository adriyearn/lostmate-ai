<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('browse.index', ['tab' => 'found']) }}" class="small fw-semibold text-muted d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Back to found items
        </a>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="lm-stat-icon lm-tone-green"><i class="bi bi-box-seam"></i></span>
                <div>
                    <div class="small text-muted fw-semibold">Found item</div>
                    <h1>{{ $foundItem->item_name }}</h1>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($foundItem->isAtOffice())
                    <span class="badge text-bg-dark"><i class="bi bi-building"></i> At the office</span>
                @endif
                <x-status-badge :status="$foundItem->status" />
                <x-report-button :action="route('found-items.report', $foundItem)" id="reportFoundItemModal" />
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card overflow-hidden mb-4">
                @if ($foundItem->images->isNotEmpty())
                    <div id="itemGallery" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner" style="background: var(--lm-gradient-soft);">
                            @foreach ($foundItem->images as $image)
                                <div class="carousel-item @if ($loop->first) active @endif">
                                    <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $foundItem->item_name }}" style="height: 420px; object-fit: contain;">
                                </div>
                            @endforeach
                        </div>
                        @if ($foundItem->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#itemGallery" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#itemGallery" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                @else
                    <div class="lm-thumb-empty" style="height: 320px; background: var(--lm-gradient-soft);">
                        <i class="bi bi-image"></i> No photos provided
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="lm-section-title mb-3"><i class="bi bi-card-text"></i> Description</h2>
                    <p class="mb-0" style="white-space: pre-line;">{{ $foundItem->description }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="lm-section-title mb-3"><i class="bi bi-info-circle"></i> Details</h2>
                    <div class="d-flex flex-column gap-3">
                        @foreach ([
                            ['icon' => 'bi-tag', 'label' => 'Category', 'value' => $foundItem->category->name],
                            ['icon' => 'bi-palette', 'label' => 'Color', 'value' => $foundItem->color ?? '—'],
                            ['icon' => 'bi-award', 'label' => 'Brand', 'value' => $foundItem->brand ?? '—'],
                            ['icon' => 'bi-geo-alt', 'label' => 'Location found', 'value' => $foundItem->location_found],
                            ['icon' => 'bi-calendar3', 'label' => 'Date found', 'value' => $foundItem->date_found->format('M j, Y').($foundItem->time_found ? ' at '.\Illuminate\Support\Carbon::parse($foundItem->time_found)->format('g:i A') : '')],
                            ['icon' => 'bi-pin-map', 'label' => 'Currently at', 'value' => $foundItem->current_location ?? '—'],
                        ] as $row)
                            <div class="d-flex align-items-center gap-3">
                                <span class="lm-stat-icon lm-tone-slate" style="width:38px;height:38px;font-size:1rem;border-radius:10px;"><i class="bi {{ $row['icon'] }}"></i></span>
                                <div>
                                    <div class="small text-muted">{{ $row['label'] }}</div>
                                    <div class="fw-semibold text-dark">{{ $row['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center gap-3">
                        <x-avatar :user="$foundItem->user" size="lg" />
                        <div>
                            <div class="small text-muted">Found by</div>
                            <div class="fw-semibold text-dark">{{ $foundItem->user->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @can('viewHiddenDetails', $foundItem)
                <div class="card mb-3 lm-secret">
                    <div class="card-body">
                        <h2 class="lm-section-title mb-1" style="font-size: 1rem;">
                            <i class="bi bi-eye-slash"></i> Hidden Details
                            <span class="badge text-bg-warning">Private</span>
                        </h2>
                        <p class="small text-muted mb-2">Only you and administrators can see this. Use it to verify claims.</p>
                        <p class="mb-0 fw-semibold text-dark">{{ $foundItem->hidden_details ?: 'No hidden details recorded.' }}</p>
                    </div>
                </div>
            @endcan

            @if ($foundItem->isAtOffice())
                {{-- Everyone sees where the item is and who handles it now. --}}
                <div class="alert alert-secondary small mb-3">
                    <i class="bi bi-building"></i>
                    This item is being held at the <strong>{{ config('lostmate.office.name') }}</strong>
                    ({{ config('lostmate.office.hours') }}). Office staff review claims and hand it over.
                </div>
            @elseif (auth()->id() === $foundItem->user_id && ! in_array($foundItem->status, [App\Enums\ItemStatus::Returned, App\Enums\ItemStatus::Closed], true))
                <div class="alert alert-secondary small mb-3">
                    <i class="bi bi-building"></i>
                    Can't hand it over yourself? Bring it to the <strong>{{ config('lostmate.office.name') }}</strong>
                    ({{ config('lostmate.office.hours') }}). Staff will mark it received and take over the claims.
                </div>
            @endif

            @if (auth()->user()->isAdmin() && ! $foundItem->isAtOffice() && ! in_array($foundItem->status, [App\Enums\ItemStatus::Returned, App\Enums\ItemStatus::Closed], true))
                <form method="POST" action="{{ route('admin.office.receive', $foundItem) }}" class="d-grid mb-3"
                      onsubmit="return confirm('Confirm the finder handed this item in at the office?');">
                    @csrf
                    <button type="submit" class="btn btn-dark"><i class="bi bi-building-check"></i> Mark as received at office</button>
                </form>
            @endif

            <div class="card">
                <div class="card-body d-grid gap-2">
                    @can('viewMatches', $foundItem)
                        <a href="{{ route('found-items.matches', $foundItem) }}" class="btn btn-primary">
                            <i class="bi bi-stars"></i> View Possible Matches
                        </a>
                    @endcan

                    @if (auth()->id() !== $foundItem->user_id)
                        <form method="POST" action="{{ route('found-items.contact', $foundItem) }}" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-chat-dots"></i> This might be mine / Contact finder
                            </button>
                        </form>

                        @can('create', [App\Models\Claim::class, $foundItem])
                            @if (in_array($foundItem->status, [App\Enums\ItemStatus::Open, App\Enums\ItemStatus::Matched], true))
                                <a href="{{ route('claims.create', $foundItem) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-patch-check"></i> Claim this item
                                </a>
                            @endif
                        @endcan
                    @endif

                    @can('update', $foundItem)
                        <a href="{{ route('found-items.claims', $foundItem) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-inboxes"></i> View Claims ({{ $foundItem->claims()->count() }})
                        </a>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('found-items.edit', $foundItem) }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-pencil"></i> Edit</a>

                            @if ($foundItem->status === App\Enums\ItemStatus::Open && auth()->user()->can('delete', $foundItem))
                                <form method="POST" action="{{ route('found-items.withdraw', $foundItem) }}" class="flex-fill d-grid"
                                      onsubmit="return confirm('Withdraw and close this report?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-archive"></i> Withdraw</button>
                                </form>
                            @endif

                            @can('delete', $foundItem)
                                <form method="POST" action="{{ route('found-items.destroy', $foundItem) }}" class="flex-fill d-grid"
                                      onsubmit="return confirm('Delete this found item report? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button><i class="bi bi-trash"></i> Delete</x-danger-button>
                                </form>
                            @endcan
                        </div>

                        @can('rerunMatching', $foundItem)
                            <form method="POST" action="{{ route('found-items.rerun-matching', $foundItem) }}" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Re-run Matching</button>
                            </form>
                        @endcan
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

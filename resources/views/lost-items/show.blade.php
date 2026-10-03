<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('browse.index', ['tab' => 'lost']) }}" class="small fw-semibold text-muted d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Back to lost items
        </a>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="lm-stat-icon lm-tone-red"><i class="bi bi-exclamation-circle"></i></span>
                <div>
                    <div class="small text-muted fw-semibold">Lost item</div>
                    <h1>{{ $lostItem->item_name }}</h1>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-status-badge :status="$lostItem->status" />
                <x-report-button :action="route('lost-items.report', $lostItem)" id="reportLostItemModal" />
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card overflow-hidden mb-4">
                @if ($lostItem->images->isNotEmpty())
                    <div id="itemGallery" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner" style="background: var(--lm-gradient-soft);">
                            @foreach ($lostItem->images as $image)
                                <div class="carousel-item @if ($loop->first) active @endif">
                                    <img src="{{ $image->url }}" class="d-block w-100" alt="{{ $lostItem->item_name }}" style="height: 420px; object-fit: contain;">
                                </div>
                            @endforeach
                        </div>
                        @if ($lostItem->images->count() > 1)
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
                    <p class="mb-0" style="white-space: pre-line;">{{ $lostItem->description }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="lm-section-title mb-3"><i class="bi bi-info-circle"></i> Details</h2>
                    <div class="d-flex flex-column gap-3">
                        @foreach ([
                            ['icon' => 'bi-tag', 'label' => 'Category', 'value' => $lostItem->category->name],
                            ['icon' => 'bi-palette', 'label' => 'Color', 'value' => $lostItem->color ?? '—'],
                            ['icon' => 'bi-award', 'label' => 'Brand', 'value' => $lostItem->brand ?? '—'],
                            ['icon' => 'bi-geo-alt', 'label' => 'Location lost', 'value' => $lostItem->location_lost],
                            ['icon' => 'bi-calendar3', 'label' => 'Date lost', 'value' => $lostItem->date_lost->format('M j, Y').($lostItem->time_lost ? ' at '.\Illuminate\Support\Carbon::parse($lostItem->time_lost)->format('g:i A') : '')],
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

                    <a href="{{ route('users.show', $lostItem->user) }}" class="d-flex align-items-center gap-3 text-decoration-none" title="View profile">
                        <x-avatar :user="$lostItem->user" size="lg" />
                        <div>
                            <div class="small text-muted">Reported by</div>
                            <div class="fw-semibold text-dark">{{ $lostItem->user->name }} <i class="bi bi-chevron-right small text-muted"></i></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-grid gap-2">
                    @can('viewMatches', $lostItem)
                        <a href="{{ route('lost-items.matches', $lostItem) }}" class="btn btn-primary">
                            <i class="bi bi-stars"></i> View Possible Matches
                        </a>
                    @endcan

                    @if (auth()->id() !== $lostItem->user_id)
                        <form method="POST" action="{{ route('lost-items.contact', $lostItem) }}" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-chat-dots"></i> I found this / Contact owner
                            </button>
                        </form>
                    @endif

                    @if ($lostItem->status === App\Enums\ItemStatus::Returned && auth()->id() === $lostItem->user_id)
                        <form method="POST" action="{{ route('lost-items.received', $lostItem) }}" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2-circle"></i> Received, close report
                            </button>
                        </form>
                    @endif

                    @can('update', $lostItem)
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('lost-items.edit', $lostItem) }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-pencil"></i> Edit</a>

                            @if ($lostItem->status === App\Enums\ItemStatus::Open)
                                <form method="POST" action="{{ route('lost-items.withdraw', $lostItem) }}" class="flex-fill d-grid"
                                      onsubmit="return confirm('Withdraw and close this report?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-archive"></i> Withdraw</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('lost-items.destroy', $lostItem) }}" class="flex-fill d-grid"
                                  onsubmit="return confirm('Delete this lost item report? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button><i class="bi bi-trash"></i> Delete</x-danger-button>
                            </form>
                        </div>

                        @can('rerunMatching', $lostItem)
                            <form method="POST" action="{{ route('lost-items.rerun-matching', $lostItem) }}" class="d-grid">
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

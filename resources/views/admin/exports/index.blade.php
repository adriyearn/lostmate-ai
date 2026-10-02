<x-admin-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Reports &amp; export</h1>
            <p class="text-muted mb-0">
                Summary for <strong class="text-dark">{{ $from->format('M j, Y') }}</strong>
                to <strong class="text-dark">{{ $to->format('M j, Y') }}</strong>
                <span class="d-none lm-print-only">&middot; generated {{ now()->format('M j, Y g:i A') }}</span>
            </p>
        </div>
    </x-slot>

    {{-- Date range + actions. Hidden when printing. --}}
    <form method="GET" action="{{ route('admin.exports.index') }}" class="card mb-4 lm-no-print">
        <div class="card-body d-flex flex-wrap align-items-end gap-3">
            <div>
                <label for="from" class="form-label small">From</label>
                <input id="from" type="date" name="from" value="{{ $from->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div>
                <label for="to" class="form-label small">To</label>
                <input id="to" type="date" name="to" value="{{ $to->toDateString() }}" class="form-control form-control-sm">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-funnel"></i> Update</button>

            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.exports.csv', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
                   class="btn btn-sm btn-primary"><i class="bi bi-filetype-csv"></i> Download CSV</a>
                <button type="button" class="btn btn-sm btn-dark" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
            </div>
            @error('to') <div class="invalid-feedback d-block w-100">{{ $message }}</div> @enderror
        </div>
    </form>

    <div class="row row-cols-2 row-cols-lg-5 g-3 mb-4">
        @foreach ([
            ['label' => 'Lost reports', 'value' => $summary['lost'], 'icon' => 'bi-exclamation-circle', 'tone' => 'lm-tone-red'],
            ['label' => 'Found reports', 'value' => $summary['found'], 'icon' => 'bi-box-seam', 'tone' => 'lm-tone-green'],
            ['label' => 'Returned to owner', 'value' => $summary['returned'], 'icon' => 'bi-arrow-return-left', 'tone' => 'lm-tone-violet'],
            ['label' => 'Still open', 'value' => $summary['open'], 'icon' => 'bi-folder2-open', 'tone' => 'lm-tone-sky'],
            ['label' => 'Recovery rate', 'value' => $summary['recoveryRate'].'%', 'icon' => 'bi-graph-up-arrow', 'tone' => 'lm-tone-slate'],
        ] as $card)
            <div class="col">
                <div class="card h-100">
                    <div class="lm-stat-card">
                        <span class="lm-stat-icon {{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}"></i></span>
                        <div>
                            <div class="lm-stat">{{ $card['value'] }}</div>
                            <div class="lm-stat-label">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        @foreach ([
            ['title' => 'Where things get lost or found most', 'icon' => 'bi-geo-alt', 'rows' => $topLocations],
            ['title' => 'Most reported categories', 'icon' => 'bi-tags', 'rows' => $topCategories],
        ] as $panel)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="lm-section-title mb-4"><i class="bi {{ $panel['icon'] }}"></i> {{ $panel['title'] }}</h2>

                        @if ($panel['rows']->isEmpty())
                            <p class="text-muted small mb-0">No reports in this period.</p>
                        @else
                            @php $max = $panel['rows']->max(); @endphp
                            <div class="d-flex flex-column gap-3">
                                @foreach ($panel['rows'] as $label => $count)
                                    <div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold text-dark">{{ $label }}</span>
                                            <span class="lm-mono text-muted">{{ $count }}</span>
                                        </div>
                                        <div class="progress" role="progressbar" aria-label="{{ $label }}" aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $max }}">
                                            <div class="progress-bar" style="width: {{ round($count / $max * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="small text-muted mt-4 mb-0">
        <i class="bi bi-info-circle"></i>
        Recovery rate = found items handed back to their owner &divide; all found items in this period.
        The CSV lists every report in the period; it never includes hidden details, emails, or phone numbers.
    </p>
</x-admin-layout>

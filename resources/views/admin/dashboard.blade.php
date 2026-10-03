<x-admin-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Admin dashboard</h1>
            <p class="text-muted mb-0">An overview of activity across LostMate AI.</p>
        </div>
    </x-slot>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        @foreach ([
            ['label' => 'Users', 'value' => $stats['users'], 'icon' => 'bi-people', 'tone' => 'lm-tone-indigo'],
            ['label' => 'Lost items', 'value' => $stats['lostItems'], 'icon' => 'bi-exclamation-circle', 'tone' => 'lm-tone-red'],
            ['label' => 'Found items', 'value' => $stats['foundItems'], 'icon' => 'bi-box-seam', 'tone' => 'lm-tone-green'],
            ['label' => 'Open reports', 'value' => $stats['openReports'], 'icon' => 'bi-folder2-open', 'tone' => 'lm-tone-sky'],
            ['label' => 'Returned items', 'value' => $stats['returnedItems'], 'icon' => 'bi-arrow-return-left', 'tone' => 'lm-tone-violet'],
            ['label' => 'Pending claims', 'value' => $stats['pendingClaims'], 'icon' => 'bi-patch-question', 'tone' => 'lm-tone-amber'],
            ['label' => 'Pending flags', 'value' => $stats['pendingFlags'], 'icon' => 'bi-flag', 'tone' => 'lm-tone-pink'],
            ['label' => 'Recovery rate', 'value' => $stats['recoveryRate'].'%', 'icon' => 'bi-graph-up-arrow', 'tone' => 'lm-tone-slate'],
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

    {{-- System health: is the background worker keeping up? --}}
    @php
        $workerLooksOff = $health['oldestWaitingMinutes'] !== null && $health['oldestWaitingMinutes'] >= 5;
    @endphp
    <div class="card mb-4 {{ $health['failed'] > 0 || $workerLooksOff ? 'border-warning' : '' }}">
        <div class="card-body d-flex flex-wrap align-items-center gap-4">
            <h2 class="lm-section-title mb-0"><i class="bi bi-activity"></i> System health</h2>

            <div class="small">
                <div class="lm-stat-label">Waiting jobs</div>
                <div class="fw-bold text-dark">{{ $health['waiting'] }}
                    @if ($health['oldestWaitingMinutes'] !== null)
                        <span class="text-muted fw-normal">(oldest {{ $health['oldestWaitingMinutes'] }} min)</span>
                    @endif
                </div>
            </div>

            <div class="small">
                <div class="lm-stat-label">Failed jobs</div>
                <div class="fw-bold {{ $health['failed'] > 0 ? 'text-danger' : 'text-dark' }}">{{ $health['failed'] }}</div>
            </div>

            <div class="small flex-grow-1">
                @if ($workerLooksOff)
                    <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Jobs have waited 5+ minutes. The queue worker may be off &mdash; AI matching and emails are paused.</span>
                @elseif ($health['failed'] > 0)
                    <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Some jobs failed (often the AI service being busy). Retry them below.</span>
                @else
                    <span class="text-muted"><i class="bi bi-check-circle"></i> All good &mdash; AI matching and emails are running.</span>
                @endif
            </div>

            @if ($health['failed'] > 0)
                <form method="POST" action="{{ route('admin.system.retry-failed') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-arrow-repeat"></i> Retry failed jobs</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="lm-section-title mb-3"><i class="bi bi-bar-chart"></i> Reports and recovery rate per month</h2>
            <canvas id="reportsChart" height="90"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('reportsChart');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'Lost',
                            data: @json($chartData['lost']),
                            backgroundColor: '#e5484d', borderColor: '#1a1917', borderWidth: 1.5, borderRadius: 6,
                        },
                        {
                            label: 'Found',
                            data: @json($chartData['found']),
                            backgroundColor: '#0f9d8a', borderColor: '#1a1917', borderWidth: 1.5, borderRadius: 6,
                        },
                        {
                            // Line on its own 0-100% axis on the right.
                            type: 'line',
                            label: 'Recovery rate (%)',
                            data: @json($chartData['recovery']),
                            yAxisID: 'percent',
                            borderColor: '#ff6a2b', backgroundColor: '#ff6a2b', borderWidth: 2.5, tension: 0.3, pointRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Reports' } },
                        percent: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, ticks: { callback: function (v) { return v + '%'; } } },
                    },
                },
            });
        });
    </script>
    @endpush
</x-admin-layout>

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

    <div class="card">
        <div class="card-body">
            <h2 class="lm-section-title mb-3"><i class="bi bi-bar-chart"></i> Reports per month</h2>
            <canvas id="reportsChart" height="90"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
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
                            backgroundColor: '#6366f1', borderRadius: 8,
                        },
                        {
                            label: 'Found',
                            data: @json($chartData['found']),
                            backgroundColor: '#c4b5fd', borderRadius: 8,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        });
    </script>
    @endpush
</x-admin-layout>

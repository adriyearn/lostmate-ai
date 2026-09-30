<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Admin Dashboard</h1>
    </x-slot>

    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        @foreach ([
            ['label' => 'Users', 'value' => $stats['users']],
            ['label' => 'Lost Items', 'value' => $stats['lostItems']],
            ['label' => 'Found Items', 'value' => $stats['foundItems']],
            ['label' => 'Open Reports', 'value' => $stats['openReports']],
            ['label' => 'Returned Items', 'value' => $stats['returnedItems']],
            ['label' => 'Pending Claims', 'value' => $stats['pendingClaims']],
            ['label' => 'Pending Flags', 'value' => $stats['pendingFlags']],
            ['label' => 'Recovery Rate', 'value' => $stats['recoveryRate'].'%'],
        ] as $card)
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="small text-muted">{{ $card['label'] }}</div>
                        <div class="h3 mb-0">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="h6">Reports per Month</h2>
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
                            backgroundColor: '#0d6efd',
                        },
                        {
                            label: 'Found',
                            data: @json($chartData['found']),
                            backgroundColor: '#198754',
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

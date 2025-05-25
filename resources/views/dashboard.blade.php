@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="chart-box mt-5">
    <h5 class="text-dark mb-4">Total Posts: {{ $totalPosts }}</h5>
    <div class="d-flex justify-content-center">
        <div style="position: relative; height: 250px; width: 250px;">
            <canvas id="itemsChart"></canvas>
        </div>
    </div>
    <div class="legend mt-4">
        <div class="legend-item text-muted">
            <span class="legend-circle bg-lost"></span> Lost ({{ $lostPercentage }}%)
        </div>
        <div class="legend-item text-muted">
            <span class="legend-circle bg-found"></span> Found ({{ $foundPercentage }}%)
        </div>
        <div class="legend-item text-muted">
            <span class="legend-circle bg-claims"></span> Claimed ({{ $claimedPercentage }}%)
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('itemsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Lost', 'Found', 'Claimed'],
                datasets: [{
                    data: [@json($lostCount), @json($foundCount), @json($claimedCount)],
                    backgroundColor: ['#6366F1', '#60A5FA', '#A5B4FC'],
                    borderWidth: 0
                }]
            },
            options: {
                cutoutPercentage: 70,
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            const label = data.labels[tooltipItem.index];
                            const value = data.datasets[0].data[tooltipItem.index];
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} items (${percentage}%)`;
                        }
                    }
                }
            }
        });
    });
</script>
@endsection

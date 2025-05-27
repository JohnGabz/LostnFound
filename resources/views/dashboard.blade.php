@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Posts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $insights['today_posts'] }}
                                @if($insights['yesterday_posts'] > 0)
                                    <small class="text-{{ $insights['today_posts'] >= $insights['yesterday_posts'] ? 'success' : 'warning' }}">
                                        @if($insights['today_posts'] >= $insights['yesterday_posts'])
                                            <i class="fas fa-arrow-up"></i>
                                        @else
                                            <i class="fas fa-arrow-down"></i>
                                        @endif
                                        {{ abs($insights['today_posts'] - $insights['yesterday_posts']) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Success Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $insights['success_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pending Claims
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $insights['pending_claims'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Users (7d)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $insights['active_users'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Existing Doughnut Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Items Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-box">
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
                </div>
            </div>
        </div>

        <!-- New Weekly Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">7-Day Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="weeklyTrendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info Row -->
    <div class="row">
        <!-- Top Categories -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Categories</h6>
                </div>
                <div class="card-body">
                    @if($topCategories->count() > 0)
                        @foreach($topCategories as $category)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-tag text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small font-weight-bold">{{ $category->category }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="small text-gray-500">{{ $category->count }} items</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No categories yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        @foreach($recentActivity as $activity)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-{{ $activity['type'] == 'lost' ? 'danger' : 'success' }}">
                                        <i class="fas fa-{{ $activity['type'] == 'lost' ? 'search' : 'box' }} text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small font-weight-bold">
                                        <a href="{{ route('items.show', $activity['id']) }}" class="text-decoration-none">
                                            {{ $activity['title'] }}
                                        </a>
                                    </div>
                                    <div class="small text-gray-500">
                                        {{ ucfirst($activity['type']) }} by {{ $activity['user'] }} • {{ $activity['time'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary { background-color: #4e73df !important; }
.bg-success { background-color: #1cc88a !important; }
.bg-danger { background-color: #e74a3b !important; }
.text-primary { color: #4e73df !important; }
.text-success { color: #1cc88a !important; }
.text-info { color: #36b9cc !important; }
.text-warning { color: #f6c23e !important; }
.text-gray-800 { color: #5a5c69 !important; }
.text-gray-500 { color: #858796 !important; }

/* Keep existing legend styles */
.legend-circle {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}
.bg-lost { background-color: #6366F1; }
.bg-found { background-color: #60A5FA; }
.bg-claims { background-color: #A5B4FC; }
.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Existing Doughnut Chart
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} items (${percentage}%)`;
                        }
                    }
                }
            }
        });

        // New Weekly Trend Chart
        const trendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($weeklyTrend['dates']),
                datasets: [{
                    label: 'Lost Items',
                    data: @json($weeklyTrend['lost']),
                    borderColor: '#e74a3b',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Found Items',
                    data: @json($weeklyTrend['found']),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    });
</script>
@endsection
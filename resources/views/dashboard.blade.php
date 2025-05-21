@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Posts</h5>
                                    <p class="card-text display-4">{{ $totalPosts }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div id="itemsChart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Lost</h5>
                                    <p class="card-text">{{ $lostCount }} items</p>
                                    <p class="card-text">{{ $lostPercentage }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Found</h5>
                                    <p class="card-text">{{ $foundCount }} items</p>
                                    <p class="card-text">{{ $foundPercentage }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Claimed</h5>
                                    <p class="card-text">{{ $claimedCount }} items</p>
                                    <p class="card-text">{{ $claimedPercentage }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sample data for the chart
        const data = {
            labels: ['Lost', 'Found', 'Claimed'],
            datasets: [{
                data: [{{ $lostCount }}, {{ $foundCount }}, {{ $claimedCount }}],
                backgroundColor: ['#007bff', '#28a745', '#17a2b8']
            }]
        };

        // Create doughnut chart
        const ctx = document.getElementById('itemsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Items Distribution'
                }
            }
        });
    });
</script>
@endsection
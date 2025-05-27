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
                                    {{ $insights['today_posts'] ?? 0 }}
                                    @if(isset($insights['yesterday_posts']) && $insights['yesterday_posts'] > 0)
                                        <small
                                            class="text-{{ ($insights['today_posts'] ?? 0) >= $insights['yesterday_posts'] ? 'success' : 'warning' }}">
                                            @if(($insights['today_posts'] ?? 0) >= $insights['yesterday_posts'])
                                                <i class="fas fa-arrow-up"></i>
                                            @else
                                                <i class="fas fa-arrow-down"></i>
                                            @endif
                                            {{ abs(($insights['today_posts'] ?? 0) - $insights['yesterday_posts']) }}
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
                                    {{ $insights['success_rate'] ?? 0 }}%
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
                                    {{ $insights['pending_claims'] ?? 0 }}
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
                                    {{ $insights['active_users'] ?? 0 }}
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
            <!-- Fixed Doughnut Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Items Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-box">
                            <h5 class="text-dark mb-4">Total Posts: {{ $totalPosts ?? 0 }}</h5>
                            <div class="d-flex justify-content-center">
                                <div style="position: relative; height: 250px; width: 250px;">
                                    <canvas id="itemsChart" width="250" height="250"></canvas>
                                </div>
                            </div>
                            <div class="legend mt-4">
                                <div class="legend-item text-muted">
                                    <span class="legend-circle" style="background-color: #e74a3b; width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> 
                                    Lost ({{ $lostPercentage ?? 0 }}%)
                                </div>
                                <div class="legend-item text-muted">
                                    <span class="legend-circle" style="background-color: #1cc88a; width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> 
                                    Found ({{ $foundPercentage ?? 0 }}%)
                                </div>
                                <div class="legend-item text-muted">
                                    <span class="legend-circle" style="background-color: #36b9cc; width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> 
                                    Claimed ({{ $claimedPercentage ?? 0 }}%)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Trend Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">7-Day Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="weeklyTrendChart" style="height: 300px;"></canvas>
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
                        @if(isset($topCategories) && $topCategories->count() > 0)
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
                        @if(isset($recentActivity) && $recentActivity->count() > 0)
                            @foreach($recentActivity as $activity)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="mr-3">
                                        <div
                                            class="icon-circle bg-{{ $activity['type'] == 'item_lost' ? 'danger' : ($activity['type'] == 'item_found' ? 'success' : 'info') }}">
                                            <i
                                                class="fas fa-{{ $activity['type'] == 'item_lost' ? 'search' : ($activity['type'] == 'item_found' ? 'box' : 'check-circle') }} text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="small font-weight-bold">
                                            <a href="{{ route('items.show', $activity['id']) }}" class="text-decoration-none">
                                                {{ $activity['title'] }}
                                            </a>
                                        </div>
                                        <div class="small text-gray-500">
                                            {{ ucfirst(str_replace('item_', '', $activity['type'])) }} {{ $activity['action'] }} by
                                            {{ $activity['user'] }} • {{ $activity['time'] }}
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

    <!-- User Management -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
                    <span class="badge bg-primary text-white">Total Users: {{ $totalUsers ?? 0 }}</span>
                </div>
                <div class="card-body">
                    @if(isset($recentUsers) && $recentUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->role ?? 'User' }}</td>
                                            <td>{{ $user->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.edit', $user->user_id) }}"
                                                    class="btn btn-sm btn-primary me-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="showDeleteModal('{{ $user->user_id }}', '{{ $user->name }}', '{{ $user->email }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted mb-0">No users found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger" id="deleteUserModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm User Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-user-times fa-3x text-danger opacity-50"></i>
                        </div>
                        <p class="mb-2">Are you sure you want to delete this user?</p>
                        <div class="alert alert-warning" role="alert">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                This action cannot be undone. All user data will be permanently removed.
                            </small>
                        </div>
                    </div>
                    
                    <!-- User Info Display -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-4 text-muted small">Name:</div>
                                <div class="col-8 font-weight-bold" id="deleteUserName"></div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-4 text-muted small">Email:</div>
                                <div class="col-8" id="deleteUserEmail"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmation Input -->
                    <div class="mb-3">
                        <label for="confirmDeleteInput" class="form-label small text-muted">
                            Type <strong>DELETE</strong> to confirm:
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="confirmDeleteInput" 
                               placeholder="Type DELETE here"
                               autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" 
                            class="btn btn-danger" 
                            id="confirmDeleteBtn" 
                            disabled
                            onclick="deleteUser()">
                        <i class="fas fa-trash me-1"></i>Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteUserForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

@endsection

@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // Global variable to store user ID for deletion
        let userToDelete = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Get chart data with defaults
            const lostCount = @json($lostCount ?? 0);
            const foundCount = @json($foundCount ?? 0);  
            const claimedCount = @json($claimedCount ?? 0);
            const weeklyTrend = @json($weeklyTrend ?? []);
            
            // Ensure weeklyTrend has required structure
            if (!weeklyTrend.dates) weeklyTrend.dates = [];
            if (!weeklyTrend.lost) weeklyTrend.lost = [];
            if (!weeklyTrend.found) weeklyTrend.found = [];
            if (!weeklyTrend.claimed) weeklyTrend.claimed = [];

            // Debug: Log data to console
            console.log('Chart data:', {
                lost: lostCount,
                found: foundCount,
                claimed: claimedCount,
                weeklyTrend: weeklyTrend
            });

            // Check if we have any data for the doughnut chart
            const totalItems = lostCount + foundCount + claimedCount;
            
            // Fixed Doughnut Chart
            const ctx = document.getElementById('itemsChart');
            if (ctx) {
                // Destroy existing chart if it exists
                if (window.itemsChart && typeof window.itemsChart.destroy === 'function') {
                    window.itemsChart.destroy();
                }

                // Create chart with proper data handling
                window.itemsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Lost', 'Found', 'Claimed'],
                        datasets: [{
                            data: totalItems > 0 ? [lostCount, foundCount, claimedCount] : [1, 1, 1], // Show equal segments if no data
                            backgroundColor: totalItems > 0 ? ['#e74a3b', '#1cc88a', '#36b9cc'] : ['#e9ecef', '#e9ecef', '#e9ecef'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: totalItems > 0, // Disable tooltip if no data
                                callbacks: {
                                    label: function (context) {
                                        if (totalItems === 0) return '';
                                        const label = context.label;
                                        const value = context.parsed;
                                        const percentage = totalItems > 0 ? ((value / totalItems) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} items (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                });

                // Add "No Data" text if no items
                if (totalItems === 0) {
                    const canvasPosition = ctx.getBoundingClientRect();
                    const parentElement = ctx.parentElement;
                    
                    // Create or update no data message
                    let noDataElement = parentElement.querySelector('.no-data-message');
                    if (!noDataElement) {
                        noDataElement = document.createElement('div');
                        noDataElement.className = 'no-data-message';
                        noDataElement.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #6c757d; font-size: 14px; font-weight: 500; pointer-events: none; z-index: 10;';
                        noDataElement.innerHTML = '<i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>No data available';
                        parentElement.style.position = 'relative';
                        parentElement.appendChild(noDataElement);
                    }
                }
            } else {
                console.error('itemsChart canvas not found');
            }

            // Weekly Trend Chart
            const trendCtx = document.getElementById('weeklyTrendChart');
            if (trendCtx) {
                // Destroy existing chart if it exists
                if (window.weeklyTrendChart && typeof window.weeklyTrendChart.destroy === 'function') {
                    window.weeklyTrendChart.destroy();
                }

                window.weeklyTrendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: weeklyTrend.dates || [],
                        datasets: [{
                            label: 'Lost Items',
                            data: weeklyTrend.lost || [],
                            borderColor: '#e74a3b',
                            backgroundColor: 'rgba(231, 74, 59, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#e74a3b',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }, {
                            label: 'Found Items',
                            data: weeklyTrend.found || [],
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#1cc88a',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }, {
                            label: 'Claimed Items',
                            data: weeklyTrend.claimed || [],
                            borderColor: '#36b9cc',
                            backgroundColor: 'rgba(54, 185, 204, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#36b9cc',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: '#858796'
                                },
                                grid: {
                                    color: 'rgba(133, 135, 150, 0.1)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#858796'
                                },
                                grid: {
                                    color: 'rgba(133, 135, 150, 0.1)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    color: '#5a5c69'
                                }
                            }
                        },
                        elements: {
                            point: {
                                hoverRadius: 8
                            }
                        }
                    }
                });
            } else {
                console.error('weeklyTrendChart canvas not found');
            }

            // Modal delete confirmation input handler
            const confirmInput = document.getElementById('confirmDeleteInput');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            if (confirmInput && confirmBtn) {
                confirmInput.addEventListener('input', function() {
                    if (this.value.toUpperCase() === 'DELETE') {
                        confirmBtn.disabled = false;
                        confirmBtn.classList.remove('btn-danger');
                        confirmBtn.classList.add('btn-danger');
                    } else {
                        confirmBtn.disabled = true;
                    }
                });

                // Reset form when modal is hidden
                const modal = document.getElementById('deleteUserModal');
                if (modal) {
                    modal.addEventListener('hidden.bs.modal', function() {
                        confirmInput.value = '';
                        confirmBtn.disabled = true;
                        userToDelete = null;
                    });
                }
            }
        });

        // Function to show delete modal
        function showDeleteModal(userId, userName, userEmail) {
            userToDelete = userId;
            
            // Update modal content
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteUserEmail').textContent = userEmail;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        }

        // Function to delete user
        function deleteUser() {
            if (userToDelete) {
                const form = document.getElementById('deleteUserForm');
                form.action = `/admin/users/${userToDelete}`;
                form.submit();
            }
        }
    </script>
@endsection
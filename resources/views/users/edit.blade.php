@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-edit text-primary me-2"></i>Edit User
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">User Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- User Edit Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-cog me-2"></i>User Information
                    </h6>
                    <div class="badge bg-info text-white">
                        ID: {{ $user->user_id }}
                    </div>
                </div>
                <div class="card-body">
                    <!-- Display Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- User Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                    <div class="small text-muted">Member Since</div>
                                    <div class="font-weight-bold">{{ $user->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-clock fa-2x text-success mb-2"></i>
                                    <div class="small text-muted">Last Updated</div>
                                    <div class="font-weight-bold">{{ $user->updated_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                                    <div class="small text-muted">Current Role</div>
                                    <div class="font-weight-bold">{{ $user->role ?? 'User' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST" id="editUserForm">
                        @csrf 
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label font-weight-bold">
                                        <i class="fas fa-user me-1 text-primary"></i>Full Name
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           value="{{ old('name', $user->name) }}" 
                                           class="form-control @error('name') is-invalid @enderror"
                                           placeholder="Enter full name"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label font-weight-bold">
                                        <i class="fas fa-envelope me-1 text-primary"></i>Email Address
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email"
                                           value="{{ old('email', $user->email) }}" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="Enter email address"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="role" class="form-label font-weight-bold">
                                        <i class="fas fa-user-tag me-1 text-primary"></i>User Role
                                    </label>
                                    <select name="role" 
                                            id="role" 
                                            class="form-control @error('role') is-invalid @enderror">
                                        <option value="">Select Role</option>
                                        <option value="User" {{ old('role', $user->role) == 'User' ? 'selected' : '' }}>User</option>
                                        <option value="Admin" {{ old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="Moderator" {{ old('role', $user->role) == 'Moderator' ? 'selected' : '' }}>Moderator</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Choose the appropriate role for this user
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label font-weight-bold">
                                        <i class="fas fa-toggle-on me-1 text-primary"></i>Account Status
                                    </label>
                                    <select name="status" 
                                            id="status" 
                                            class="form-control @error('status') is-invalid @enderror">
                                        <option value="active" {{ old('status', $user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $user->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $user->status ?? 'active') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Control user access to the system
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold text-dark mb-3">
                                            <i class="fas fa-info-circle me-2 text-info"></i>Additional Information
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="phone" class="form-label">
                                                        <i class="fas fa-phone me-1 text-muted"></i>Phone Number
                                                    </label>
                                                    <input type="tel" 
                                                           name="phone" 
                                                           id="phone"
                                                           value="{{ old('phone', $user->phone ?? '') }}" 
                                                           class="form-control"
                                                           placeholder="Enter phone number (optional)">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="department" class="form-label">
                                                        <i class="fas fa-building me-1 text-muted"></i>Department
                                                    </label>
                                                    <input type="text" 
                                                           name="department" 
                                                           id="department"
                                                           value="{{ old('department', $user->department ?? '') }}" 
                                                           class="form-control"
                                                           placeholder="Enter department (optional)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>Last updated: {{ $user->updated_at->format('M d, Y \a\t g:i A') }}
                                    </div>
                                    <div>
                                        <a href="{{ route('dashboard') }}" class="btn btn-secondary me-2">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="fas fa-save me-1"></i>Update User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone Card -->
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="text-danger font-weight-bold mb-1">Delete User Account</h6>
                            <p class="text-muted mb-0 small">
                                Permanently delete this user account and all associated data. This action cannot be undone.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="showDeleteModal('{{ $user->user_id }}', '{{ $user->name }}', '{{ $user->email }}')">
                                <i class="fas fa-trash me-1"></i>Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal (Same as Dashboard) -->
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
</div>
@endsection

@section('scripts')
<script>
    // Global variable to store user ID for deletion
    let userToDelete = null;

    document.addEventListener('DOMContentLoaded', function () {
        // Form validation enhancement
        const form = document.getElementById('editUserForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
                submitBtn.disabled = true;
            });
        }

        // Modal delete confirmation input handler
        const confirmInput = document.getElementById('confirmDeleteInput');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (confirmInput && confirmBtn) {
            confirmInput.addEventListener('input', function() {
                if (this.value.toUpperCase() === 'DELETE') {
                    confirmBtn.disabled = false;
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

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
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
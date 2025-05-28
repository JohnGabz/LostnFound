@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-edit text-primary me-2"></i>Edit Profile
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
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
            <!-- Profile Edit Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-cog me-2"></i>Your Information
                    </h6>
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

                    <form method="POST" action="{{ route('profile.update') }}" id="editProfileForm">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label font-weight-bold">
                                        <i class="fas fa-user me-1 text-primary"></i>Name <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        id="name" 
                                        type="text" 
                                        name="name" 
                                        value="{{ old('name', auth()->user()->name) }}" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        required 
                                        autofocus
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label font-weight-bold">
                                        <i class="fas fa-envelope me-1 text-primary"></i>Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email', auth()->user()->email) }}" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- ADDED: Contact Number Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label font-weight-bold">
                                        <i class="fas fa-phone me-1 text-primary"></i>Contact Number <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        id="contact_number" 
                                        type="tel" 
                                        name="contact_number" 
                                        value="{{ old('contact_number', auth()->user()->contact_number) }}" 
                                        class="form-control @error('contact_number') is-invalid @enderror" 
                                        required
                                        maxlength="11"
                                        placeholder="09XX XXX XXXX"
                                    >
                                    <small class="form-text text-muted">Philippine mobile number (11 digits starting with 09)</small>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-eye me-1 text-primary"></i>Contact Visibility
                                    </label>
                                    <div class="form-check mt-2">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            id="show_contact_publicly" 
                                            name="show_contact_publicly" 
                                            value="1"
                                            {{ old('show_contact_publicly', auth()->user()->show_contact_publicly) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="show_contact_publicly">
                                            <strong>Make my contact number visible to other users</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        When enabled, other users can see your contact number on items you post.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- ADDED: Current Contact Display Preview -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle text-info me-1"></i>Current Contact Display
                                    </h6>
                                    <div id="contactPreview">
                                        @if(auth()->user()->shouldShowContact())
                                            <div class="text-success">
                                                <i class="fas fa-eye"></i> <strong>Visible to others:</strong> 
                                                {{ auth()->user()->public_contact }}
                                                <br>
                                                <small class="text-muted">
                                                    Other users will see this contact information on items you post.
                                                </small>
                                            </div>
                                        @else
                                            <div class="text-warning">
                                                <i class="fas fa-eye-slash"></i> <strong>Hidden from others</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Your contact number is private. Other users can only contact you through the claims system.
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label font-weight-bold">
                                        <i class="fas fa-key me-1 text-primary"></i>New Password 
                                        <small class="text-muted">(Leave blank to keep current password)</small>
                                    </label>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        autocomplete="new-password"
                                    >
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="password-confirm" class="form-label font-weight-bold">
                                        Confirm New Password
                                    </label>
                                    <input 
                                        id="password-confirm" 
                                        type="password" 
                                        name="password_confirmation" 
                                        class="form-control" 
                                        autocomplete="new-password"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ADDED: Contact Information Help Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle me-2"></i>Contact Information Help
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Why provide contact information?</h6>
                            <ul class="mb-3">
                                <li>Faster communication about lost/found items</li>
                                <li>Direct contact via phone calls, SMS, or WhatsApp</li>
                                <li>Better coordination for item pickup/return</li>
                                <li>Increased trust between users</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Privacy & Safety:</h6>
                            <ul class="mb-3">
                                <li>You control whether your contact is visible or private</li>
                                <li>Only shown on items you post (not on your profile)</li>
                                <li>You can change this setting anytime</li>
                                <li>Report any misuse of contact information</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Tip:</strong> Even with private contact settings, you can still communicate through our secure claims system.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('editProfileForm');
        const submitBtn = document.getElementById('submitBtn');
        const contactInput = document.getElementById('contact_number');
        const contactCheckbox = document.getElementById('show_contact_publicly');
        const contactPreview = document.getElementById('contactPreview');

        if (form) {
            form.addEventListener('submit', function () {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
                submitBtn.disabled = true;
            });
        }

        // Format contact number input
        if (contactInput) {
            contactInput.addEventListener('input', function() {
                // Remove all non-digits
                let value = this.value.replace(/\D/g, '');
                
                // Limit to 11 digits
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }
                
                // Ensure it starts with 09
                if (value.length >= 2 && !value.startsWith('09')) {
                    if (value.startsWith('9')) {
                        value = '0' + value;
                    } else if (!value.startsWith('0')) {
                        value = '09' + value.substring(0, 9);
                    }
                }
                
                this.value = value;
                
                // Visual feedback
                if (value.length === 11 && value.startsWith('09')) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    if (value.length > 0) {
                        this.classList.add('is-invalid');
                    }
                }

                // Update preview
                updateContactPreview();
            });
        }

        // Update contact visibility preview
        if (contactCheckbox) {
            contactCheckbox.addEventListener('change', updateContactPreview);
        }

        function updateContactPreview() {
            const contact = contactInput.value;
            const isVisible = contactCheckbox.checked;
            
            if (contact.length === 11 && contact.startsWith('09')) {
                const formattedContact = '+63' + contact.substring(1);
                
                if (isVisible) {
                    contactPreview.innerHTML = `
                        <div class="text-success">
                            <i class="fas fa-eye"></i> <strong>Will be visible to others:</strong> 
                            ${formattedContact}
                            <br>
                            <small class="text-muted">
                                Other users will see this contact information on items you post.
                            </small>
                        </div>
                    `;
                } else {
                    contactPreview.innerHTML = `
                        <div class="text-warning">
                            <i class="fas fa-eye-slash"></i> <strong>Will be hidden from others</strong>
                            <br>
                            <small class="text-muted">
                                Your contact number will be private. Other users can only contact you through the claims system.
                            </small>
                        </div>
                    `;
                }
            } else {
                contactPreview.innerHTML = `
                    <div class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        <small>Enter a valid contact number to see preview</small>
                    </div>
                `;
            }
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });

        // Initial preview update
        updateContactPreview();
    });
</script>
@endsection
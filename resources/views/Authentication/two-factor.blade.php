@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mr-3" 
                             style="width: 48px; height: 48px;">
                            <i class="fas fa-envelope-open-text fa-lg"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-weight-bold text-dark">Email Two-Factor Authentication</h4>
                            <p class="mb-0 text-muted">Secure your account with email verification codes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if ($isEnabled)
                <!-- 2FA is ENABLED -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Status Header -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mr-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-success">Email 2FA is Active</h5>
                                    <p class="mb-0 text-muted">Verification codes will be sent to <strong>{{ $user->email }}</strong></p>
                                </div>
                            </div>
                            <span class="badge badge-success badge-pill px-3 py-2">
                                <i class="fas fa-shield-alt mr-1"></i> ENABLED
                            </span>
                        </div>

                        <!-- How it works -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fas fa-info-circle mr-2"></i>How Email 2FA Works
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-sign-in-alt"></i>
                                        </div>
                                        <h6 class="font-weight-bold">1. Login</h6>
                                        <small class="text-muted">Enter your email and password</small>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-warning text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <h6 class="font-weight-bold">2. Check Email</h6>
                                        <small class="text-muted">We send a 6-digit code</small>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-key"></i>
                                        </div>
                                        <h6 class="font-weight-bold">3. Verify</h6>
                                        <small class="text-muted">Enter the code to access</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border-danger">
                            <div class="card-header bg-light border-danger">
                                <h6 class="mb-0 font-weight-bold text-danger">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Disable Email Two-Factor Authentication
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">
                                    <i class="fas fa-warning mr-1 text-warning"></i>
                                    Disabling two-factor authentication will make your account less secure. You will no longer receive email verification codes when logging in.
                                </p>
                                
                                <form method="POST" action="{{ route('two-factor.disable') }}" id="disable2faForm">
                                    @csrf
                                    @method('DELETE')
                                    <div class="form-group">
                                        <label for="password" class="font-weight-bold">Confirm Your Password</label>
                                        <input type="password" name="password" id="password" required 
                                               class="form-control" placeholder="Enter your current password">
                                    </div>
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to disable email two-factor authentication? This will make your account less secure.')">
                                        <i class="fas fa-shield-alt mr-1"></i> Disable Email 2FA
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- 2FA is DISABLED - Setup Flow -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Status Header -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning text-white d-flex justify-content-center align-items-center mr-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 font-weight-bold text-warning">Email 2FA is Disabled</h5>
                                    <p class="mb-0 text-muted">Enable email verification for enhanced security</p>
                                </div>
                            </div>
                            <span class="badge badge-warning badge-pill px-3 py-2">
                                <i class="fas fa-shield-alt mr-1"></i> DISABLED
                            </span>
                        </div>

                        <!-- Benefits Section -->
                        <div class="alert alert-info" role="alert">
                            <h6 class="font-weight-bold mb-2">
                                <i class="fas fa-shield-check mr-2"></i>Why Enable Email Two-Factor Authentication?
                            </h6>
                            <ul class="mb-0">
                                <li><strong>Enhanced Security:</strong> Protects your account even if your password is compromised</li>
                                <li><strong>Email Convenience:</strong> No need to install additional apps - codes sent directly to your email</li>
                                <li><strong>Time-Limited Codes:</strong> Verification codes expire in 5 minutes for maximum security</li>
                                <li><strong>Easy Setup:</strong> Enable with just one click - no complex configuration needed</li>
                            </ul>
                        </div>

                        <!-- Current Email -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-envelope mr-2"></i>Verification Email Address
                                </h6>
                                <p class="mb-0">
                                    OTP codes will be sent to: <strong class="text-primary">{{ $user->email }}</strong>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Make sure you have access to this email address before enabling 2FA.
                                </small>
                            </div>
                        </div>

                        <!-- How it will work -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fas fa-cogs mr-2"></i>How Email 2FA Will Work
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <span class="font-weight-bold">1</span>
                                        </div>
                                        <h6 class="font-weight-bold">Login Attempt</h6>
                                        <small class="text-muted">You enter your email and password</small>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-warning text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <span class="font-weight-bold">2</span>
                                        </div>
                                        <h6 class="font-weight-bold">Email Sent</h6>
                                        <small class="text-muted">6-digit code sent to your email</small>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mx-auto mb-2" 
                                             style="width: 50px; height: 50px;">
                                            <span class="font-weight-bold">3</span>
                                        </div>
                                        <h6 class="font-weight-bold">Access Granted</h6>
                                        <small class="text-muted">Enter code and login successfully</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enable Button -->
                        <div class="text-center">
                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-shield-alt mr-2"></i>Enable Email Two-Factor Authentication
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
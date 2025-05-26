@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
            <p class="mt-1 text-sm text-gray-600">
                Add additional security to your account using two-factor authentication.
            </p>
        </div>

        <div class="px-6 py-4">
            @if (session('status'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (auth()->user()->hasEnabledTwoFactorAuthentication())
                <!-- 2FA is enabled -->
                <div class="space-y-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-lg font-medium text-gray-900">Two-factor authentication is enabled</h4>
                            <p class="text-sm text-gray-600">Your account is protected with two-factor authentication.</p>
                        </div>
                    </div>

                    <!-- Recovery Codes -->
                    @if ($recoveryCodes)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <h5 class="text-sm font-medium text-yellow-800 mb-2">Recovery Codes</h5>
                            <p class="text-sm text-yellow-700 mb-3">
                                Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                            </p>
                            <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                @foreach ($recoveryCodes as $code)
                                    <div class="bg-white px-3 py-2 rounded border">{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                            @csrf
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition">
                                Regenerate Recovery Codes
                            </button>
                        </form>
                    @endif

                    <!-- Disable 2FA -->
                    <div class="border-t border-gray-200 pt-6">
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Disable Two-Factor Authentication</h5>
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-3">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                                Disable Two-Factor Authentication
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- 2FA is not enabled -->
                <div class="space-y-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Enable Two-Factor Authentication</h4>
                        <p class="text-sm text-gray-600 mb-4">
                            Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.
                        </p>
                    </div>

                    <!-- QR Code -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h5 class="text-sm font-medium text-gray-900 mb-3">1. Scan QR Code</h5>
                        <p class="text-sm text-gray-600 mb-4">
                            Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.):
                        </p>
                        <div class="flex justify-center mb-4">
                            <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" class="border border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500 text-center">
                            Or manually enter this secret: <code class="bg-gray-100 px-2 py-1 rounded">{{ $secret }}</code>
                        </p>
                    </div>

                    <!-- Verify Code -->
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 mb-3">2. Verify Setup</h5>
                        <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Enter the 6-digit code from your authenticator app</label>
                                <input type="text" name="code" maxlength="6" required 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="123456">
                            </div>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                                Enable Two-Factor Authentication
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
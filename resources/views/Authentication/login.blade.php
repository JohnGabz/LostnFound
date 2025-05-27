<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .password-strength {
      height: 4px;
      transition: all 0.3s ease;
    }
    .show-password-btn {
      cursor: pointer;
      user-select: none;
    }
    .loading {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-white">
  <div class="flex w-full h-screen">
    <!-- Left panel -->
    <div class="w-1/2 bg-gradient-to-b from-indigo-600 via-indigo-400 to-indigo-100 flex items-center justify-center text-white flex-col">
      <!-- Logo and Brand -->
      <svg class="w-12 h-12 mb-4" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="white" stroke-width="4.5"/>
      </svg>
      <h1 class="text-3xl font-bold">LostnFound</h1>
      <p class="text-indigo-100 mt-2 text-center">Secure access to your account</p>
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex items-center justify-center p-10">
      <div class="w-full max-w-md space-y-6">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900">Login</h2>
          <p class="text-gray-500">Please login to your account</p>
        </div>

        <!-- Display success message -->
        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <!-- Display status message -->
        @if (session('status'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ session('status') }}</span>
        </div>
        @endif

        <!-- Display validation errors -->
        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <div class="font-medium">Please correct the following errors:</div>
          <ul class="mt-2 space-y-1">
            @foreach ($errors->all() as $error)
            <li class="text-sm">• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6" id="loginForm" novalidate>
          @csrf
          
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-gray-700 text-sm mb-1 font-medium">
              Email Address <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input 
                type="email" 
                id="email"
                name="email" 
                placeholder="Enter your email address" 
                value="{{ old('email') }}" 
                required 
                autocomplete="email"
                aria-describedby="email-error"
                class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('email') border-red-500 bg-red-50 @enderror" 
              />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 25 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill="currentColor" d="M4.27 2.5C2.59 2.5 1.22 3.62 1.22 5V15C1.22 16.38 2.59 17.5 4.27 17.5H20.56C22.24 17.5 23.61 16.38 23.61 15V5C23.61 3.62 22.24 2.5 20.56 2.5H4.27ZM6.96 6.03C6.53 5.73 5.89 5.78 5.53 6.13C5.17 6.49 5.23 7.01 5.66 7.31L10.46 10.58C11.59 11.36 13.24 11.36 14.37 10.58L19.17 7.31C19.6 7.01 19.66 6.49 19.3 6.13C18.94 5.78 18.3 5.73 17.87 6.03L13.07 9.3C12.69 9.56 12.14 9.56 11.76 9.3L6.96 6.03Z" />
                </svg>
              </div>
            </div>
            @error('email')
            <p class="mt-1 text-sm text-red-600" id="email-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-gray-700 text-sm mb-1 font-medium">
              Password <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input 
                type="password" 
                id="password"
                name="password" 
                placeholder="Enter your password" 
                required 
                autocomplete="current-password"
                aria-describedby="password-error"
                class="w-full pl-10 pr-12 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('password') border-red-500 bg-red-50 @enderror" 
              />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <button 
                type="button" 
                id="togglePassword"
                class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 show-password-btn"
                aria-label="Toggle password visibility"
              >
                <svg class="w-5 h-5" id="eyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
            @error('password')
            <p class="mt-1 text-sm text-red-600" id="password-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Remember Me Checkbox -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input 
                id="remember" 
                name="remember" 
                type="checkbox" 
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                {{ old('remember') ? 'checked' : '' }}
              >
              <label for="remember" class="ml-2 block text-sm text-gray-700">
                Remember me
              </label>
            </div>
            <!-- Forgot Password Link -->
            <div class="text-sm">
              <a href="{{ route('password.request') }}" class="text-indigo-600 hover:text-indigo-500 hover:underline transition duration-200">
                Forgot password?
              </a>
            </div>
          </div>

          <!-- Login Button -->
          <div>
            <button 
              type="submit" 
              id="loginButton"
              class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span id="buttonText">Sign In</span>
              <span id="loadingSpinner" class="hidden">
                <svg class="inline w-4 h-4 mr-2 loading" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
              </span>
            </button>
          </div>
        </form>

        <!-- Sign up link -->
        <div class="text-sm text-center text-gray-500">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-500 hover:underline transition duration-200 font-medium ml-1">
            Create an account
          </a>
        </div>

        <!-- Security Notice -->
        <div class="text-xs text-center text-gray-400 mt-4">
          <p>🔒 Your connection is secure and encrypted</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Password visibility toggle
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');

      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Update icon
        if (type === 'text') {
          eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
          `;
        } else {
          eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          `;
        }
      });

      // Form submission handling
      const loginForm = document.getElementById('loginForm');
      const loginButton = document.getElementById('loginButton');
      const buttonText = document.getElementById('buttonText');
      const loadingSpinner = document.getElementById('loadingSpinner');

      loginForm.addEventListener('submit', function(e) {
        // Show loading state
        loginButton.disabled = true;
        buttonText.classList.add('hidden');
        loadingSpinner.classList.remove('hidden');
      });

      // Email validation on blur
      const emailInput = document.getElementById('email');
      emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
          this.classList.add('border-red-500', 'bg-red-50');
        } else {
          this.classList.remove('border-red-500', 'bg-red-50');
        }
      });

      // Password strength indicator (optional)
      const passwordInput2 = document.getElementById('password');
      passwordInput2.addEventListener('input', function() {
        // Remove error styling when user starts typing
        this.classList.remove('border-red-500', 'bg-red-50');
      });

      // Email validation helper
      function isValidEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
      }

      // Auto-hide alerts after 5 seconds
      setTimeout(function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(function(alert) {
          if (!alert.classList.contains('bg-red-100')) { // Don't auto-hide error messages
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
              alert.remove();
            }, 500);
          }
        });
      }, 5000);
    });
  </script>
</body>
</html>
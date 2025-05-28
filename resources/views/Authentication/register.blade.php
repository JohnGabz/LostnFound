<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sign Up - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .password-strength {
      height: 3px;
      transition: all 0.3s ease;
      border-radius: 2px;
    }
    .strength-weak { background-color: #ef4444; }
    .strength-fair { background-color: #f59e0b; }
    .strength-good { background-color: #10b981; }
    .strength-strong { background-color: #059669; }
    
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
    .field-valid {
      border-color: #10b981 !important;
      background-color: #f0fdf4 !important;
    }
    .field-invalid {
      border-color: #ef4444 !important;
      background-color: #fef2f2 !important;
    }

    /* Custom scrollbar for form */
    .form-scroll::-webkit-scrollbar {
      width: 6px;
    }
    .form-scroll::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 3px;
    }
    .form-scroll::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }
    .form-scroll::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>
</head>
<body class="min-h-screen bg-white overflow-hidden">
  <div class="flex w-full h-screen">
    <!-- Left panel -->
    <div class="w-1/2 bg-gradient-to-b from-indigo-600 via-indigo-400 to-indigo-100 flex items-center justify-center text-white flex-col">
      <!-- Logo and Brand -->
      <svg class="w-10 h-10 mb-3" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="white" stroke-width="4.5"/>
      </svg>
      <h1 class="text-2xl font-bold mb-2">LostnFound</h1>
      <p class="text-indigo-100 text-center text-sm">Join our secure platform</p>
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex flex-col h-screen">
      <!-- Header -->
      <div class="px-8 pt-6 pb-4">
        <h2 class="text-xl font-semibold text-gray-900">Create Account</h2>
        <p class="text-gray-500 text-sm">Please fill in your information below</p>
      </div>

      <!-- Scrollable Form Container -->
      <div class="flex-1 overflow-y-auto form-scroll px-8 pb-6">
        <!-- Display messages -->
        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm mb-4" role="alert">
          <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm mb-4" role="alert">
          <div class="font-medium">Please correct the following errors:</div>
          <ul class="mt-1 space-y-1">
            @foreach ($errors->all() as $error)
            <li class="text-xs">• {{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('register') }}" method="POST" class="space-y-4" id="registerForm" novalidate>
          @csrf

          <!-- Name -->
          <div>
            <label for="name" class="block text-gray-700 text-xs mb-1 font-medium">
              Full Name <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input 
                type="text" 
                id="name"
                name="name" 
                placeholder="Enter your full name" 
                value="{{ old('name') }}" 
                required 
                autocomplete="name"
                class="w-full pl-8 pr-3 py-2 text-sm rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('name') field-invalid @enderror" 
              />
              <div class="absolute left-2.5 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div id="nameCheckmark" class="absolute right-2.5 top-2.5 text-green-500 hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @else
            <p class="mt-1 text-xs text-gray-500">Use your real name (2-100 characters)</p>
            @enderror
          </div>

          <!-- Contact Number -->
          <div>
            <label for="contact_number" class="block text-gray-700 text-xs mb-1 font-medium">
              Contact Number <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input 
                type="tel" 
                id="contact_number"
                name="contact_number" 
                placeholder="09XX XXX XXXX" 
                value="{{ old('contact_number') }}" 
                required 
                autocomplete="tel"
                maxlength="11"
                class="w-full pl-8 pr-3 py-2 text-sm rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('contact_number') field-invalid @enderror" 
              />
              <div class="absolute left-2.5 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <div id="contactCheckmark" class="absolute right-2.5 top-2.5 text-green-500 hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            @error('contact_number')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @else
            <p class="mt-1 text-xs text-gray-500">Philippine mobile number (11 digits starting with 09)</p>
            @enderror
          </div>

          <!-- Show Contact Publicly -->
          <div class="flex items-start space-x-2">
            <div class="flex items-center h-4">
              <input 
                id="show_contact_publicly" 
                name="show_contact_publicly" 
                type="checkbox" 
                value="1"
                {{ old('show_contact_publicly') ? 'checked' : '' }}
                class="h-3 w-3 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              >
            </div>
            <div class="text-xs">
              <label for="show_contact_publicly" class="text-gray-700">
                Allow other users to see my contact number when I post items
              </label>
              <p class="text-xs text-gray-500 mt-0.5">This helps other users contact you directly about lost/found items</p>
            </div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-gray-700 text-xs mb-1 font-medium">
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
                class="w-full pl-8 pr-3 py-2 text-sm rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('email') field-invalid @enderror" 
              />
              <div class="absolute left-2.5 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 25 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill="currentColor" d="M4.27 2.5C2.59 2.5 1.22 3.62 1.22 5V15C1.22 16.38 2.59 17.5 4.27 17.5H20.56C22.24 17.5 23.61 16.38 23.61 15V5C23.61 3.62 22.24 2.5 20.56 2.5H4.27ZM6.96 6.03C6.53 5.73 5.89 5.78 5.53 6.13C5.17 6.49 5.23 7.01 5.66 7.31L10.46 10.58C11.59 11.36 13.24 11.36 14.37 10.58L19.17 7.31C19.6 7.01 19.66 6.49 19.3 6.13C18.94 5.78 18.3 5.73 17.87 6.03L13.07 9.3C12.69 9.56 12.14 9.56 11.76 9.3L6.96 6.03Z" />
                </svg>
              </div>
              <div id="emailCheckmark" class="absolute right-2.5 top-2.5 text-green-500 hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @else
            <p class="mt-1 text-xs text-gray-500">We'll send a verification link to this email</p>
            @enderror
          </div>

          <!-- Passwords Row -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Password -->
            <div>
              <label for="password" class="block text-gray-700 text-xs mb-1 font-medium">
                Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input 
                  type="password" 
                  id="password"
                  name="password" 
                  placeholder="Create password" 
                  required 
                  autocomplete="new-password"
                  class="w-full pl-8 pr-8 py-2 text-sm rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('password') field-invalid @enderror" 
                />
                <div class="absolute left-2.5 top-2.5 text-gray-400">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <button 
                  type="button" 
                  id="togglePassword"
                  class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 show-password-btn"
                >
                  <svg class="w-4 h-4" id="passwordEyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              @error('password')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="password_confirmation" class="block text-gray-700 text-xs mb-1 font-medium">
                Confirm Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input 
                  type="password" 
                  id="password_confirmation"
                  name="password_confirmation" 
                  placeholder="Confirm password" 
                  required 
                  autocomplete="new-password"
                  class="w-full pl-8 pr-8 py-2 text-sm rounded-lg bg-gray-50 text-gray-900 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 @error('password_confirmation') field-invalid @enderror" 
                />
                <div class="absolute left-2.5 top-2.5 text-gray-400">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <button 
                  type="button" 
                  id="togglePasswordConfirm"
                  class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 show-password-btn"
                >
                  <svg class="w-4 h-4" id="confirmEyeIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <div id="confirmCheckmark" class="absolute right-7 top-2.5 text-green-500 hidden">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
              @error('password_confirmation')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Password Requirements -->
          <div class="mt-2">
            <div class="password-strength bg-gray-200" id="passwordStrength"></div>
            <p class="mt-1 text-xs" id="passwordStrengthText">Password strength will appear here</p>
            <div class="mt-1 text-xs text-gray-500">
              <div class="flex flex-wrap gap-1 text-xs">
                <span id="length-check" class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">8+ chars</span>
                <span id="uppercase-check" class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Upper</span>
                <span id="lowercase-check" class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Lower</span>
                <span id="number-check" class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Number</span>
                <span id="symbol-check" class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Symbol</span>
              </div>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start space-x-2">
            <div class="flex items-center h-4">
              <input 
                id="terms" 
                name="terms" 
                type="checkbox" 
                required
                class="h-3 w-3 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              >
            </div>
            <div class="text-xs">
              <label for="terms" class="text-gray-700">
                I agree to the 
                <a href="#" class="text-indigo-600 hover:text-indigo-500 hover:underline">Terms of Service</a> 
                and 
                <a href="#" class="text-indigo-600 hover:text-indigo-500 hover:underline">Privacy Policy</a>
                <span class="text-red-500">*</span>
              </label>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button 
              type="submit" 
              id="registerButton"
              class="w-full py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center text-sm"
            >
              <span id="buttonText">Create Account</span>
              <span id="loadingSpinner" class="hidden">
                <svg class="inline w-3 h-3 mr-2 loading" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating account...
              </span>
              <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </form>

        <!-- Already have account -->
        <div class="text-xs text-center text-gray-500 mt-4">
          Already have an account?
          <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 hover:underline transition duration-200 font-medium ml-1">
            Sign in here
          </a>
        </div>

        <!-- Security Notice -->
        <div class="text-xs text-center text-gray-400 mt-2">
          <p>🔒 Your information is secure and encrypted</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Form elements
      const nameInput = document.getElementById('name');
      const contactInput = document.getElementById('contact_number');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('password_confirmation');
      const registerForm = document.getElementById('registerForm');
      const registerButton = document.getElementById('registerButton');
      const buttonText = document.getElementById('buttonText');
      const loadingSpinner = document.getElementById('loadingSpinner');

      // Password visibility toggles
      setupPasswordToggle('togglePassword', 'password', 'passwordEyeIcon');
      setupPasswordToggle('togglePasswordConfirm', 'password_confirmation', 'confirmEyeIcon');

      // Real-time validation
      nameInput.addEventListener('input', validateName);
      contactInput.addEventListener('input', validateContact);
      emailInput.addEventListener('blur', validateEmail);
      passwordInput.addEventListener('input', validatePassword);
      confirmPasswordInput.addEventListener('input', validatePasswordConfirmation);

      // Form submission
      registerForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
          e.preventDefault();
          return;
        }
        
        // Show loading state
        registerButton.disabled = true;
        buttonText.classList.add('hidden');
        loadingSpinner.classList.remove('hidden');
      });

      function setupPasswordToggle(buttonId, inputId, iconId) {
        const toggle = document.getElementById(buttonId);
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        toggle.addEventListener('click', function() {
          const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
          input.setAttribute('type', type);
          
          if (type === 'text') {
            icon.innerHTML = `
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
            `;
          } else {
            icon.innerHTML = `
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
          }
        });
      }

      function validateName() {
        const name = nameInput.value.trim();
        const nameCheckmark = document.getElementById('nameCheckmark');
        
        if (name.length >= 2 && name.length <= 100 && /^[a-zA-Z\s\-\.\']+$/.test(name)) {
          nameInput.classList.remove('field-invalid');
          nameInput.classList.add('field-valid');
          nameCheckmark.classList.remove('hidden');
          return true;
        } else {
          nameInput.classList.remove('field-valid');
          nameInput.classList.add('field-invalid');
          nameCheckmark.classList.add('hidden');
          return false;
        }
      }

      function validateContact() {
        const contact = contactInput.value.trim();
        const contactCheckmark = document.getElementById('contactCheckmark');
        
        // Remove non-digits for validation
        const digits = contact.replace(/\D/g, '');
        
        // Check if it's a valid Philippine mobile number (11 digits starting with 09)
        const isValid = /^09\d{9}$/.test(digits);
        
        if (isValid) {
          contactInput.classList.remove('field-invalid');
          contactInput.classList.add('field-valid');
          contactCheckmark.classList.remove('hidden');
          return true;
        } else {
          contactInput.classList.remove('field-valid');
          contactInput.classList.add('field-invalid');
          contactCheckmark.classList.add('hidden');
          return false;
        }
      }

      function validateEmail() {
        const email = emailInput.value.trim();
        const emailCheckmark = document.getElementById('emailCheckmark');
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (email && emailRegex.test(email)) {
          emailInput.classList.remove('field-invalid');
          emailInput.classList.add('field-valid');
          emailCheckmark.classList.remove('hidden');
          return true;
        } else {
          emailInput.classList.remove('field-valid');
          emailInput.classList.add('field-invalid');
          emailCheckmark.classList.add('hidden');
          return false;
        }
      }

      function validatePassword() {
        const password = passwordInput.value;
        const strength = calculatePasswordStrength(password);
        updatePasswordStrength(strength);
        updatePasswordChecks(password);
        
        // Also validate confirmation if it has a value
        if (confirmPasswordInput.value) {
          validatePasswordConfirmation();
        }
        
        return strength >= 3; // Good or Strong
      }

      function validatePasswordConfirmation() {
        const password = passwordInput.value;
        const confirm = confirmPasswordInput.value;
        const confirmCheckmark = document.getElementById('confirmCheckmark');
        
        if (confirm && password === confirm) {
          confirmPasswordInput.classList.remove('field-invalid');
          confirmPasswordInput.classList.add('field-valid');
          confirmCheckmark.classList.remove('hidden');
          return true;
        } else {
          confirmPasswordInput.classList.remove('field-valid');
          confirmPasswordInput.classList.add('field-invalid');
          confirmCheckmark.classList.add('hidden');
          return false;
        }
      }

      function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return strength;
      }

      function updatePasswordStrength(strength) {
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');
        
        strengthBar.style.width = (strength * 20) + '%';
        
        switch(strength) {
          case 0:
          case 1:
            strengthBar.className = 'password-strength strength-weak';
            strengthText.textContent = 'Very weak';
            strengthText.className = 'mt-1 text-xs text-red-600';
            break;
          case 2:
            strengthBar.className = 'password-strength strength-weak';
            strengthText.textContent = 'Weak';
            strengthText.className = 'mt-1 text-xs text-red-600';
            break;
          case 3:
            strengthBar.className = 'password-strength strength-fair';
            strengthText.textContent = 'Fair';
            strengthText.className = 'mt-1 text-xs text-yellow-600';
            break;
          case 4:
            strengthBar.className = 'password-strength strength-good';
            strengthText.textContent = 'Good';
            strengthText.className = 'mt-1 text-xs text-green-600';
            break;
          case 5:
            strengthBar.className = 'password-strength strength-strong';
            strengthText.textContent = 'Strong';
            strengthText.className = 'mt-1 text-xs text-green-700';
            break;
        }
      }

      function updatePasswordChecks(password) {
        const checks = {
          'length-check': password.length >= 8,
          'uppercase-check': /[A-Z]/.test(password),
          'lowercase-check': /[a-z]/.test(password),
          'number-check': /[0-9]/.test(password),
          'symbol-check': /[^A-Za-z0-9]/.test(password)
        };

        Object.keys(checks).forEach(checkId => {
          const element = document.getElementById(checkId);
          if (checks[checkId]) {
            element.className = 'bg-green-100 text-green-700 px-1.5 py-0.5 rounded text-xs';
          } else {
            element.className = 'bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded text-xs';
          }
        });
      }

      function validateForm() {
        const isNameValid = validateName();
        const isContactValid = validateContact();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isConfirmValid = validatePasswordConfirmation();
        const isTermsAccepted = document.getElementById('terms').checked;

        return isNameValid && isContactValid && isEmailValid && isPasswordValid && isConfirmValid && isTermsAccepted;
      }

      // Auto-hide alerts after 5 seconds
      setTimeout(function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(function(alert) {
          if (!alert.classList.contains('bg-red-100')) {
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
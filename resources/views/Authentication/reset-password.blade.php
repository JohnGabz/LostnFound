<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-white">
  <div class="flex w-full h-screen">
    <!-- Left panel -->
    <div class="w-1/2 bg-gradient-to-b from-green-600 via-green-400 to-green-100 flex items-center justify-center text-white flex-col">
      <!-- Logo and Brand -->
      <svg class="w-12 h-12 mb-4" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="white" stroke-width="4.5"/>
      </svg>
      <h1 class="text-3xl font-bold">LostnFound</h1>
      <p class="text-green-100 mt-2">Create New Password</p>
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex items-center justify-center p-10">
      <div class="w-full max-w-md space-y-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2h6a2 2 0 012 2v2M9 12l2 2 4-4" />
            </svg>
          </div>
          <h2 class="text-2xl font-semibold text-gray-900">Create New Password</h2>
          <p class="text-gray-500">Your identity has been verified. Please set a new password.</p>
        </div>

        <!-- Display validation errors if any -->
        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif

        <!-- Display status message -->
        @if (session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">

          <!-- Email Display -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">Email Address</label>
            <div class="w-full px-4 py-3 rounded-lg bg-gray-100 text-gray-700 border border-gray-300">
              {{ $email }}
            </div>
          </div>
          
          <!-- New Password Input -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">New Password</label>
            <div class="relative">
              <input type="password" name="password" id="password" placeholder="Enter new password" 
                     required class="w-full pl-10 pr-10 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500" />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('password')">
                <i id="password-icon" class="fas fa-eye"></i>
              </button>
            </div>
            
            <!-- Password Strength Indicator -->
            <div class="mt-2">
              <div class="flex space-x-1 mb-1">
                <div id="strength-1" class="h-1 w-full bg-gray-200 rounded"></div>
                <div id="strength-2" class="h-1 w-full bg-gray-200 rounded"></div>
                <div id="strength-3" class="h-1 w-full bg-gray-200 rounded"></div>
                <div id="strength-4" class="h-1 w-full bg-gray-200 rounded"></div>
              </div>
              <p id="strength-text" class="text-xs text-gray-500">Password strength: <span id="strength-level">Enter password</span></p>
            </div>
          </div>

          <!-- Confirm Password Input -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">Confirm New Password</label>
            <div class="relative">
              <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password" 
                     required class="w-full pl-10 pr-10 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500" />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('password_confirmation')">
                <i id="password_confirmation-icon" class="fas fa-eye"></i>
              </button>
            </div>
            <p id="match-text" class="text-xs mt-1 hidden">
              <span id="match-status"></span>
            </p>
          </div>

          <!-- Password Requirements -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-800 mb-2">Password Requirements:</h4>
            <ul class="text-xs text-blue-700 space-y-1">
              <li id="req-length" class="flex items-center">
                <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                At least 8 characters long
              </li>
              <li id="req-upper" class="flex items-center">
                <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                At least one uppercase letter (A-Z)
              </li>
              <li id="req-lower" class="flex items-center">
                <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                At least one lowercase letter (a-z)
              </li>
              <li id="req-number" class="flex items-center">
                <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                At least one number (0-9)
              </li>
            </ul>
          </div>

          <!-- Submit Button -->
          <div>
            <button type="submit" id="resetBtn" disabled 
                    class="w-full py-3 bg-gray-400 text-white rounded-lg transition flex justify-center items-center cursor-not-allowed">
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Reset Password
            </button>
          </div>
        </form>

        <!-- Security notice -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-800">Security Tips</h3>
              <div class="mt-1 text-xs text-gray-600">
                <ul class="space-y-1">
                  <li>• Use a unique password you haven't used before</li>
                  <li>• Consider using a password manager</li>
                  <li>• Don't share your password with anyone</li>
                  <li>• Enable two-factor authentication for extra security</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = document.getElementById(fieldId + '-icon');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    function checkPasswordStrength(password) {
      let strength = 0;
      const requirements = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password)
      };

      // Update requirement indicators
      Object.keys(requirements).forEach(req => {
        const element = document.getElementById(`req-${req}`);
        const icon = element.querySelector('i');
        
        if (requirements[req]) {
          icon.classList.remove('fa-circle', 'text-gray-400');
          icon.classList.add('fa-check-circle', 'text-green-500');
          strength++;
        } else {
          icon.classList.remove('fa-check-circle', 'text-green-500');
          icon.classList.add('fa-circle', 'text-gray-400');
        }
      });

      // Update strength bars
      const bars = ['strength-1', 'strength-2', 'strength-3', 'strength-4'];
      const colors = ['bg-red-400', 'bg-yellow-400', 'bg-blue-400', 'bg-green-400'];
      const levels = ['Very Weak', 'Weak', 'Good', 'Strong'];
      
      bars.forEach((bar, index) => {
        const element = document.getElementById(bar);
        element.className = 'h-1 w-full rounded ' + (index < strength ? colors[strength - 1] : 'bg-gray-200');
      });

      const strengthLevel = document.getElementById('strength-level');
      strengthLevel.textContent = strength === 0 ? 'Enter password' : levels[strength - 1];
      strengthLevel.className = strength === 0 ? '' : (strength < 3 ? 'text-red-600' : 'text-green-600');

      return strength >= 3; // Require at least "Good" strength
    }

    function checkPasswordMatch() {
      const password = document.getElementById('password').value;
      const confirmation = document.getElementById('password_confirmation').value;
      const matchText = document.getElementById('match-text');
      const matchStatus = document.getElementById('match-status');

      if (confirmation.length === 0) {
        matchText.classList.add('hidden');
        return false;
      }

      matchText.classList.remove('hidden');

      if (password === confirmation) {
        matchStatus.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1"></i><span class="text-green-600">Passwords match</span>';
        return true;
      } else {
        matchStatus.innerHTML = '<i class="fas fa-times-circle text-red-500 mr-1"></i><span class="text-red-600">Passwords do not match</span>';
        return false;
      }
    }

    function updateSubmitButton() {
      const password = document.getElementById('password').value;
      const confirmation = document.getElementById('password_confirmation').value;
      const submitBtn = document.getElementById('resetBtn');

      const isStrongEnough = checkPasswordStrength(password);
      const isMatching = checkPasswordMatch();

      if (isStrongEnough && isMatching && confirmation.length > 0) {
        submitBtn.disabled = false;
        submitBtn.className = 'w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex justify-center items-center cursor-pointer';
        submitBtn.innerHTML = `
          <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Reset Password
        `;
      } else {
        submitBtn.disabled = true;
        submitBtn.className = 'w-full py-3 bg-gray-400 text-white rounded-lg transition flex justify-center items-center cursor-not-allowed';
        submitBtn.innerHTML = `
          <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Reset Password
        `;
      }
    }

    // Event listeners
    document.getElementById('password').addEventListener('input', updateSubmitButton);
    document.getElementById('password_confirmation').addEventListener('input', updateSubmitButton);

    // Focus on password field
    document.getElementById('password').focus();
  </script>
</body>
</html>
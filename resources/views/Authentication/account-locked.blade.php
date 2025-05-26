<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Locked - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
  <div class="max-w-md w-full space-y-8 p-8">
    <div class="text-center">
      <!-- Lock Icon -->
      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
        <i class="fas fa-lock text-red-600 text-2xl"></i>
      </div>
      
      <!-- Logo -->
      <div class="mb-6">
        <svg class="w-12 h-12 mx-auto mb-2" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="#6366F1" stroke-width="4.5"/>
        </svg>
        <h1 class="text-lg font-bold text-gray-900">LostnFound</h1>
      </div>

      <h2 class="text-2xl font-bold text-red-600 mb-2">Account Temporarily Locked</h2>
      <p class="text-gray-600 mb-6">Your account has been locked due to multiple failed login attempts.</p>
    </div>

    <!-- Lockout Information -->
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Security Alert</h3>
          <div class="mt-2 text-sm text-red-700">
            <p>We detected multiple failed login attempts on your account. For your security, we've temporarily locked your account.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Timer Display -->
    <div class="text-center">
      <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Account will unlock in:</h3>
        <div id="countdown" class="text-3xl font-mono font-bold text-red-600 mb-2">--:--</div>
        <p class="text-sm text-gray-500">Please wait for the lockout period to expire</p>
      </div>
    </div>

    <!-- What to do -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-info-circle text-blue-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-blue-800">What can you do?</h3>
          <div class="mt-2 text-sm text-blue-700">
            <ul class="list-disc list-inside space-y-1">
              <li>Wait for the lockout period to expire</li>
              <li>Check your email for a security notification</li>
              <li>Make sure you're using the correct password</li>
              <li>Consider enabling two-factor authentication</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Tips -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-shield-alt text-yellow-400"></i>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">If this wasn't you:</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <ul class="list-disc list-inside space-y-1">
              <li>Change your password immediately after unlock</li>
              <li>Enable two-factor authentication</li>
              <li>Contact our support team</li>
              <li>Review your recent account activity</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
      <button onclick="location.reload()" 
              class="w-full py-3 px-4 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition duration-150 ease-in-out">
        <i class="fas fa-sync-alt mr-2"></i>
        Refresh Page
      </button>
      
      <a href="{{ route('password.request') }}" 
         class="w-full py-3 px-4 border border-transparent rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition duration-150 ease-in-out text-center block">
        <i class="fas fa-key mr-2"></i>
        Reset Password Instead
      </a>
      
      <a href="{{ route('login') }}" 
         class="w-full text-center text-sm text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out block">
        ← Back to Login
      </a>
    </div>
  </div>

  <script>
    // Get lockout time from URL parameter or set default
    const urlParams = new URLSearchParams(window.location.search);
    let lockoutMinutes = parseInt(urlParams.get('minutes')) || 30;
    let timeLeft = lockoutMinutes * 60; // Convert to seconds

    const countdown = document.getElementById('countdown');
    
    function updateCountdown() {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      countdown.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      
      if (timeLeft <= 0) {
        countdown.textContent = '00:00';
        countdown.className = 'text-3xl font-mono font-bold text-green-600 mb-2';
        
        // Show unlock message
        const container = countdown.parentElement;
        container.innerHTML = `
          <h3 class="text-lg font-medium text-green-900 mb-2">Account Unlocked!</h3>
          <div class="text-3xl font-mono font-bold text-green-600 mb-2">
            <i class="fas fa-unlock text-2xl"></i>
          </div>
          <p class="text-sm text-green-600 mb-4">You can now try logging in again</p>
          <a href="{{ route('login') }}" 
             class="inline-block py-2 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            Try Login Again
          </a>
        `;
        
        return;
      }
      
      // Change color when time is running low
      if (timeLeft <= 300) { // Last 5 minutes
        countdown.className = 'text-3xl font-mono font-bold text-yellow-600 mb-2';
      }
      if (timeLeft <= 60) { // Last minute
        countdown.className = 'text-3xl font-mono font-bold text-red-600 mb-2';
      }
      
      timeLeft--;
    }

    // Update countdown immediately and then every second
    updateCountdown();
    const timer = setInterval(updateCountdown, 1000);
  </script>
</body>
</html>
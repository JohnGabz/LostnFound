<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Reset Code - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
  <div class="flex w-full max-w-6xl mx-auto shadow-2xl rounded-2xl overflow-hidden bg-white">
    <!-- Left panel -->
    <div class="w-1/2 bg-gradient-to-br from-purple-600 via-purple-500 to-purple-400 flex items-center justify-center text-white flex-col p-12 relative">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-20 h-20 border border-white rounded-full"></div>
        <div class="absolute top-32 right-16 w-16 h-16 border border-white rounded-full"></div>
        <div class="absolute bottom-20 left-20 w-12 h-12 border border-white rounded-full"></div>
        <div class="absolute bottom-32 right-12 w-8 h-8 border border-white rounded-full"></div>
      </div>
      
      <!-- Logo and Brand -->
      <div class="relative z-10 text-center">
        <div class="mb-6">
          <svg class="w-16 h-16 mx-auto mb-4" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="white" stroke-width="4.5"/>
          </svg>
        </div>
        <h1 class="text-4xl font-bold mb-2">LostnFound</h1>
        <p class="text-purple-100 text-lg">Password Recovery</p>
      </div>

      <!-- Security Features -->
      <div class="relative z-10 mt-12 space-y-4 text-left">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <i class="fas fa-key text-white"></i>
          </div>
          <span class="text-purple-100">Secure Password Reset</span>
        </div>
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <i class="fas fa-clock text-white"></i>
          </div>
          <span class="text-purple-100">10-Minute Code Expiration</span>
        </div>
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <i class="fas fa-envelope text-white"></i>
          </div>
          <span class="text-purple-100">Email Verification Required</span>
        </div>
      </div>
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex items-center justify-center p-12">
      <div class="w-full max-w-md space-y-8">
        <!-- Header -->
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-purple-100 mb-6">
            <i class="fas fa-shield-check text-purple-600 text-2xl"></i>
          </div>
          
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Enter Reset Code</h2>
          <p class="text-gray-600 mb-1">We've sent a password reset code to</p>
          <p class="text-purple-600 font-semibold">{{ $email }}</p>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700">{{ session('status') }}</p>
            </div>
          </div>
        </div>
        @endif

        <!-- Error Messages -->
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Verification Failed</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        </div>
        @endif

        <!-- OTP Form -->
        <form method="POST" action="{{ route('password.verify-otp') }}" class="space-y-6">
          @csrf
          <input type="hidden" name="email" value="{{ $email }}">
          
          <!-- Code Input -->
          <div>
            <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">
              Reset Code
            </label>
            <div class="relative">
              <input type="text" name="otp_code" id="otp_code" 
                     maxlength="6" required autofocus autocomplete="off"
                     class="w-full px-4 py-4 text-center text-3xl font-mono border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 tracking-widest"
                     placeholder="000000">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-key text-gray-400"></i>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-500 text-center">
              Enter the 6-digit code from your email
            </p>
          </div>

          <!-- Timer Display -->
          <div class="text-center">
            <div id="timer" class="text-sm text-gray-500">
              <i class="fas fa-clock mr-1"></i>
              Code expires in: <span id="countdown" class="font-mono font-semibold">10:00</span>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button type="submit" id="verifyBtn"
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-lg font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out">
              <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <i class="fas fa-check group-hover:text-purple-300"></i>
              </span>
              Verify Reset Code
            </button>
          </div>
        </form>

        <!-- Resend Section -->
        <div class="space-y-4">
          <div class="text-center">
            <button type="button" id="resendBtn" 
                    class="text-purple-600 hover:text-purple-700 text-sm font-medium transition duration-150 ease-in-out"
                    onclick="resendOtp()">
              <i class="fas fa-redo mr-1"></i>
              Didn't receive the code? Send again
            </button>
            <div id="resendMessage" class="mt-2 text-sm" style="display: none;"></div>
          </div>

          <!-- Help Section -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-gray-400"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-gray-800">Having trouble?</h3>
                <ul class="mt-1 text-xs text-gray-600 space-y-1">
                  <li>• Check your spam/junk folder</li>
                  <li>• Make sure {{ $email }} is accessible</li>
                  <li>• Code expires in 10 minutes</li>
                  <li>• Request a new code if needed</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Back to Login -->
          <div class="text-center border-t pt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out">
              <i class="fas fa-arrow-left mr-2"></i>
              Back to Login
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Auto-focus and format input
    document.getElementById('otp_code').focus();
    
    // Format input as user types
    document.getElementById('otp_code').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
      if (value.length <= 6) {
        e.target.value = value;
      }
    });

    // Auto-submit when 6 digits entered
    document.getElementById('otp_code').addEventListener('input', function(e) {
      if (e.target.value.length === 6) {
        setTimeout(() => {
          if (e.target.value.length === 6) {
            document.querySelector('form').submit();
          }
        }, 500);
      }
    });

    // Countdown timer (10 minutes)
    let timeLeft = 600; // 10 minutes in seconds
    const countdown = document.getElementById('countdown');
    const timer = setInterval(() => {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      countdown.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
      
      if (timeLeft <= 0) {
        clearInterval(timer);
        countdown.textContent = 'EXPIRED';
        countdown.className = 'font-mono font-semibold text-red-500';
        document.getElementById('timer').innerHTML = '<i class="fas fa-exclamation-triangle mr-1 text-red-500"></i>Code has expired. Please request a new one.';
      } else if (timeLeft <= 120) { // Last 2 minutes
        countdown.className = 'font-mono font-semibold text-red-500';
      }
      
      timeLeft--;
    }, 1000);

    // Resend OTP function
    async function resendOtp() {
      const btn = document.getElementById('resendBtn');
      const message = document.getElementById('resendMessage');
      
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';
      
      try {
        const response = await fetch('{{ route("password.resend-otp") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            email: '{{ $email }}'
          })
        });
        
        const data = await response.json();
        
        if (response.ok) {
          message.innerHTML = '<div class="text-green-600"><i class="fas fa-check mr-1"></i>' + data.message + '</div>';
          // Reset timer
          timeLeft = 600;
          countdown.className = 'font-mono font-semibold';
          document.getElementById('timer').innerHTML = '<i class="fas fa-clock mr-1"></i>Code expires in: <span id="countdown" class="font-mono font-semibold">10:00</span>';
        } else {
          if (data.wait_time) {
            message.innerHTML = '<div class="text-yellow-600"><i class="fas fa-clock mr-1"></i>Please wait ' + data.wait_time + ' seconds before requesting another code.</div>';
          } else {
            message.innerHTML = '<div class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>' + data.error + '</div>';
          }
        }
      } catch (error) {
        message.innerHTML = '<div class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Network error. Please try again.</div>';
      }
      
      message.style.display = 'block';
      
      setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo mr-1"></i>Didn\'t receive the code? Send again';
      }, data && data.wait_time ? data.wait_time * 1000 : 3000);
    }
  </script>
</body>
</html>
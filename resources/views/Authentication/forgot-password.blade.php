<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-white">
  <div class="flex w-full h-screen">
    <!-- Left panel -->
    <div class="w-1/2 bg-gradient-to-b from-purple-600 via-purple-400 to-purple-100 flex items-center justify-center text-white flex-col">
      <!-- Logo and Brand -->
      <svg class="w-12 h-12 mb-4" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z" stroke="white" stroke-width="4.5"/>
      </svg>
      <h1 class="text-3xl font-bold">LostnFound</h1>
      <p class="text-purple-100 mt-2">Secure Password Recovery</p>
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex items-center justify-center p-10">
      <div class="w-full max-w-md space-y-6">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2m0 0V7a2 2 0 012-2h6a2 2 0 012 2v2M9 12l2 2 4-4" />
            </svg>
          </div>
          <h2 class="text-2xl font-semibold text-gray-900">Forgot Your Password?</h2>
          <p class="text-gray-500">No worries! Enter your email and we'll send you a reset code.</p>
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

        <form method="POST" action="{{ route('password.send-otp') }}" class="space-y-6">
          @csrf
          
          <!-- Email Input -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">Email Address</label>
            <div class="relative">
              <input type="email" name="email" placeholder="Enter your email address" 
                     value="{{ old('email') }}" required autofocus
                     class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500" />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 25 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#98A2B3" d="M4.27 2.5C2.59 2.5 1.22 3.62 1.22 5V15C1.22 16.38 2.59 17.5 4.27 17.5H20.56C22.24 17.5 23.61 16.38 23.61 15V5C23.61 3.62 22.24 2.5 20.56 2.5H4.27ZM6.96 6.03C6.53 5.73 5.89 5.78 5.53 6.13C5.17 6.49 5.23 7.01 5.66 7.31L10.46 10.58C11.59 11.36 13.24 11.36 14.37 10.58L19.17 7.31C19.6 7.01 19.66 6.49 19.3 6.13C18.94 5.78 18.3 5.73 17.87 6.03L13.07 9.3C12.69 9.56 12.14 9.56 11.76 9.3L6.96 6.03Z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              We'll send a 6-digit verification code to this email address.
            </p>
          </div>

          <!-- Submit Button -->
          <div>
            <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex justify-center items-center">
              <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Send Reset Code
            </button>
          </div>
        </form>

        <!-- Back to login -->
        <div class="text-sm text-center text-gray-500">
          Remember your password?
          <a href="{{ route('login') }}" class="text-purple-600 hover:underline ml-1">Back to Login</a>
        </div>

        <!-- Help Section -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-gray-800">Need Help?</h3>
              <div class="mt-1 text-xs text-gray-600">
                <ul class="space-y-1">
                  <li>• Make sure to enter the correct email address</li>
                  <li>• Check your spam/junk folder for the reset code</li>
                  <li>• The reset code will expire in 10 minutes</li>
                  <li>• Contact support if you continue having issues</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - LostnFound</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
    </div>

    <!-- Right panel -->
    <div class="w-1/2 flex items-center justify-center p-10">
      <div class="w-full max-w-md space-y-6">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900">Login</h2>
          <p class="text-gray-500">Please login your account</p>
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

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
          @csrf
          <!-- Email Input -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">Email</label>
            <div class="relative">
              <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 25 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#98A2B3" d="M4.27 2.5C2.59 2.5 1.22 3.62 1.22 5V15C1.22 16.38 2.59 17.5 4.27 17.5H20.56C22.24 17.5 23.61 16.38 23.61 15V5C23.61 3.62 22.24 2.5 20.56 2.5H4.27ZM6.96 6.03C6.53 5.73 5.89 5.78 5.53 6.13C5.17 6.49 5.23 7.01 5.66 7.31L10.46 10.58C11.59 11.36 13.24 11.36 14.37 10.58L19.17 7.31C19.6 7.01 19.66 6.49 19.3 6.13C18.94 5.78 18.3 5.73 17.87 6.03L13.07 9.3C12.69 9.56 12.14 9.56 11.76 9.3L6.96 6.03Z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-gray-700 text-sm mb-1">Password</label>
            <div class="relative">
              <input type="password" name="password" placeholder="Password" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <div class="absolute left-3 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 25 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#98A2B3" d="M4.27 2.5C2.59 2.5 1.22 3.62 1.22 5V15C1.22 16.38 2.59 17.5 4.27 17.5H20.56C22.24 17.5 23.61 16.38 23.61 15V5C23.61 3.62 22.24 2.5 20.56 2.5H4.27ZM6.96 6.03C6.53 5.73 5.89 5.78 5.53 6.13C5.17 6.49 5.23 7.01 5.66 7.31L10.46 10.58C11.59 11.36 13.24 11.36 14.37 10.58L19.17 7.31C19.6 7.01 19.66 6.49 19.3 6.13C18.94 5.78 18.3 5.73 17.87 6.03L13.07 9.3C12.69 9.56 12.14 9.56 11.76 9.3L6.96 6.03Z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Login Button -->
          <div>
            <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Login</button>
          </div>
        </form>

        <!-- Forgot Password & Sign up links -->
        <div class="space-y-3">
          <!-- Forgot Password Link -->
          <div class="text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">
              Forgot your password?
            </a>
          </div>

          <!-- Sign up link -->
          <div class="text-sm text-center text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline ml-1">Sign up</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
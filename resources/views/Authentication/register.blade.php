<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - LostnFound</title>
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
          <h2 class="text-2xl font-semibold text-gray-900">Sign Up</h2>
          <p class="text-gray-500">Please fill your information below</p>
        </div>

        <!-- Form Starts -->
        <form action="/register" method="POST" class="space-y-4">
          @csrf

          <!-- Name -->
          <div>
            <input type="text" name="name" placeholder="Name" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div class="absolute left-3 top-3.5 text-gray-400">
              <!-- User Icon -->
            </div>
          </div>

          <!-- Email -->
          <div>
            <input type="email" name="email" placeholder="E-mail" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <!-- Password -->
          <div>
            <input type="password" name="password" placeholder="Password" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <!-- Confirm Password -->
          <div>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-100 text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <!-- Next Button -->
          <div>
            <button class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex justify-center items-center">
              Next
              <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </form>

        <!-- Already have account -->
        <div class="text-sm text-center text-gray-500">
          Already have an account?
          <a href="/login" class="text-indigo-600 hover:underline ml-1">Login to your account</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

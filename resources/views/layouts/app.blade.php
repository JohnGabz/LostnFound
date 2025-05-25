<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LostnFound') }} - @yield('title', 'Digital Lost & Found Tracker')</title>

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f5f7fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #ffffff;
            padding-top: 2rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #6c757d;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background-color: #f0f2f5;
            color: #4966e1;
        }

        .sidebar-link i {
            margin-right: 0.75rem;
        }

        .content-wrapper {
            padding: 2rem;
            background-color: #f5f7fa;
            min-height: 100vh;
        }

        .navbar {
            z-index: 1030;
        }

        .chart-box {
            background-color: #d3d4d6;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
        }

        .legend {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .bg-lost {
            background-color: #6366F1;
        }

        .bg-found {
            background-color: #60A5FA;
        }

        .bg-claims {
            background-color: #A5B4FC;
        }
    </style>

    @yield('styles')
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-none d-md-block p-3 bg-light" style="min-height: 100vh;">
            @auth
                <!-- Role and Avatar -->
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center"
                        style="width: 40px; height: 40px; font-weight: bold;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="mt-2 text-uppercase text-primary font-weight-bold small">
                        {{ strtoupper(Auth::user()->role ?? 'USER') }}
                    </div>
                </div>

                <!-- Menu Label -->
                <div class="text-muted text-uppercase small font-weight-bold mb-2 px-2">
                    Menu
                </div>

                <!-- Menu Items -->
                <nav class="nav flex-column">
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link nav-link px-3 py-2 rounded {{ Request::is('dashboard') ? 'bg-primary text-white' : 'text-dark' }}">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('found.index') }}"
                        class="sidebar-link nav-link px-3 py-2 rounded {{ Request::is('found*') ? 'bg-primary text-white' : 'text-dark' }}">
                        <i class="fas fa-shopping-basket mr-2"></i> Found Posts
                    </a>
                    <a href="{{ route('lost.index') }}"
                        class="sidebar-link nav-link px-3 py-2 rounded {{ Request::is('lost*') ? 'bg-primary text-white' : 'text-dark' }}">
                        <i class="fas fa-book-dead mr-2"></i> Lost Posts
                    </a>
                    <a href="{{ route('claims.index') }}"
                        class="sidebar-link nav-link px-3 py-2 rounded {{ Request::is('claims*') ? 'bg-primary text-white' : 'text-dark' }}">
                        <i class="fas fa-clipboard-check mr-2"></i> Claimed Posts
                    </a>
                </nav>
            @endauth
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                        <strong>
                            @php
                                $routeName = Route::currentRouteName();
                                $pageTitle = match ($routeName) {
                                    'dashboard' => 'Dashboard',
                                    'found.index' => 'Found Posts',
                                    'lost.index' => 'Lost Posts',
                                    'claims.index' => 'Claimed Posts',
                                    'items.index' => 'Items',
                                    'items.my' => 'My Items',
                                    'profile.edit' => 'Edit Profile',
                                    'login' => 'Login',
                                    'register' => 'Register',
                                    default => config('app.name', 'LostnFound'),
                                };
                            @endphp

                            {{ $pageTitle }}
                        </strong>
                    </a>

                    <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Right Side -->
                        <ul class="navbar-nav ml-auto align-items-center">
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                                @endif
                                @if (Route::has('register'))
                                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                                @endif
                            @else
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366F1&color=fff&size=32"
                                            class="rounded-circle mr-2" alt="Avatar">
                                        <span class="font-weight-semibold text-dark">{{ Auth::user()->name }}</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="fas fa-user mr-2"></i> Profile
                                        </a>
                                        <a class="dropdown-item" href="{{ route('items.my') }}">
                                            <i class="fas fa-box mr-2"></i> My Items
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf</form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="content-wrapper">
                <div class="container-fluid">
                    @includeWhen(session('success'), 'components.alert', ['type' => 'success', 'message' => session('success')])
                    @includeWhen(session('error'), 'components.alert', ['type' => 'danger', 'message' => session('error')])
                    @includeWhen(session('warning'), 'components.alert', ['type' => 'warning', 'message' => session('warning')])
                    @includeWhen(session('info'), 'components.alert', ['type' => 'info', 'message' => session('info')])

                    @yield('content')
                </div>
            </main>

            <footer class="bg-white py-4 border-top text-center">
                <p class="mb-0 text-muted">&copy; {{ date('Y') }} {{ config('app.name') }} - Digital Lost & Found
                    Tracker</p>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('scripts')
</body>

</html>
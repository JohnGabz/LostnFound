<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Lost & Found - Reuniting People with Their Belongings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            padding: 8rem 0 4rem;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }

        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-hero:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .search-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .search-header h2 {
            color: #1a1a1a;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            min-width: 250px;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            font-size: 0.9rem;
            min-width: 120px;
        }

        /* Items Grid */
        .items-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .section-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            background: #f8fafc;
            border-radius: 15px;
            padding: 0.5rem;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .tab-btn.active {
            background: white;
            color: #667eea;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .item-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .item-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #94a3b8;
        }

        .item-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .item-description {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .item-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-lost {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-found {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-claimed {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .cta-section {
            text-align: center;
            padding: 3rem 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }

        .cta-section h2 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .stat {
            text-align: center;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .search-container {
                flex-direction: column;
            }

            .filters {
                flex-direction: column;
            }

            .filter-select {
                min-width: 100%;
            }

            .stats {
                gap: 2rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeInUp 0.4s ease-out;
        }
    </style>
</head>

<body>
    <header>
        <nav class="container">
            <div class="logo">
                <svg class="w-12 h-12 mb-4" viewBox="0 0 43 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14.8057 13.0273C14.8057 20.5752 20.9248 26.6943 28.4727 26.6943H30.2617C34.0034 26.6943 37.3933 25.1899 39.8613 22.7539V30.1113C39.8612 35.1432 35.7819 39.2227 30.75 39.2227H11.3887C6.35692 39.2225 2.27746 35.1431 2.27734 30.1113V11.8887C2.27746 6.85692 6.35692 2.77746 11.3887 2.77734H18.2871C16.1225 5.19531 14.8057 8.38798 14.8057 11.8887V13.0273ZM19.3613 11.8887C19.3614 7.01425 23.1892 3.03429 28.0029 2.79004L28.4727 2.77734H30.2617C35.2936 2.77734 39.3729 6.85685 39.373 11.8887V13.0273C39.373 18.0593 35.2936 22.1387 30.2617 22.1387H28.4727C23.4407 22.1387 19.3613 18.0593 19.3613 13.0273V11.8887Z"
                        stroke="white" stroke-width="4.5" />
                </svg>
                <h1 class="text-3xl font-bold">LostnFound</h1>
            </div>
            <div class="auth-buttons">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">
                        <i class="fas fa-user-circle"></i>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                @endauth
            </div>

        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Find What's Lost, Return What's Found</h1>
                <p>Join our community-driven platform where lost items find their way home and good deeds are rewarded.
                </p>

                <div class="stats">
                    <div class="stat">
                        <span class="stat-number" id="lostCount">{{ $stats['lost'] ?? 0 }}</span>
                        <span class="stat-label">Items Lost</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="foundCount">{{ $stats['found'] ?? 0 }}</span>
                        <span class="stat-label">Items Found</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="claimedCount">{{ $stats['claimed'] ?? 0 }}</span>
                        <span class="stat-label">Successfully Reunited</span>
                    </div>
                </div>

                <div class="hero-buttons">
                    <a href="{{ auth()->check() ? route('items.report', ['type' => 'lost']) : route('register') }}"
                        class="btn btn-hero">
                        <i class="fas fa-exclamation-circle"></i>
                        Report Lost Item
                    </a>
                    <a href="{{ auth()->check() ? route('items.report', ['type' => 'found']) : route('register') }}"
                        class="btn btn-hero">
                        <i class="fas fa-hand-holding"></i>
                        Report Found Item
                    </a>
                </div>
            </div>
        </section>

        <div class="container">
            <section class="search-section fade-in-up">
                <div class="search-header">
                    <h2>Search Lost & Found Items</h2>
                    <p>Help reunite people with their belongings by searching our database</p>
                </div>

                <div class="search-container">
                    <input type="text" class="search-input"
                        placeholder="Search by item name, description, or location..." id="searchInput">
                    <button class="btn btn-primary" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </div>

                <div class="filters">
                    <select class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessories">Accessories</option>
                        <option value="bags">Bags</option>
                        <option value="keys">Keys</option>
                        <option value="documents">Documents</option>
                        <option value="other">Other</option>
                    </select>
                    <select class="filter-select" id="locationFilter">
                        <option value="">All Locations</option>
                        <option value="campus">Campus</option>
                        <option value="library">Library</option>
                        <option value="cafeteria">Cafeteria</option>
                        <option value="parking">Parking Area</option>
                        <option value="classroom">Classroom</option>
                        <option value="other">Other</option>
                    </select>
                    <select class="filter-select" id="dateFilter">
                        <option value="">Any Date</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </section>

            <section class="items-section fade-in-up">
                <div class="section-tabs">
                    <button class="tab-btn active" onclick="showTab('lost')">
                        <i class="fas fa-exclamation-circle"></i>
                        Lost Items
                    </button>
                    <button class="tab-btn" onclick="showTab('found')">
                        <i class="fas fa-hand-holding"></i>
                        Found Items
                    </button>
                    <button class="tab-btn" onclick="showTab('claimed')">
                        <i class="fas fa-check-circle"></i>
                        Success Stories
                    </button>
                </div>

                <div id="lost-content" class="tab-content active">
                    <div class="items-grid" id="lostItems">
                        @forelse($lostItems as $item)
                            <div class="item-card">
                                <div class="item-image">
                                    @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    @else
                                        <i class="{{ getCategoryIcon($item->category) }}"></i>
                                    @endif
                                </div>
                                <h3 class="item-title">{{ $item->title }}</h3>
                                <div class="item-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $item->location }}</span>
                                    <span><i class="fas fa-calendar"></i>
                                        {{ $item->date_lost_found ? $item->date_lost_found->format('M d, Y') : $item->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="item-description">
                                    {{ Str::limit($item->description ?? 'No description provided.', 100) }}
                                </p>
                                <span class="item-status status-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                                @if($item->status !== 'claimed')
                                    <div style="margin-top: 1rem; text-align: center;">
                                        <a href="{{ auth()->check() ? route('claims.store', ['item' => $item->id]) : route('register') }}"
                                            class="btn btn-primary">
                                            <i class="fas fa-hand-point-right"></i>
                                            I Found This!
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
                                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No lost items reported yet. Be the first to help someone find their belongings!</p>
                                <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-plus"></i>
                                    Report Lost Item
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div id="found-content" class="tab-content">
                    <div class="items-grid" id="foundItems">
                        @forelse($foundItems as $item)
                            <div class="item-card">
                                <div class="item-image">
                                    @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    @else
                                        <i class="{{ getCategoryIcon($item->category) }}"></i>
                                    @endif
                                </div>
                                <h3 class="item-title">{{ $item->title }}</h3>
                                <div class="item-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $item->location }}</span>
                                    <span><i class="fas fa-calendar"></i>
                                        {{ $item->date_lost_found ? $item->date_lost_found->format('M d, Y') : $item->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="item-description">
                                    {{ Str::limit($item->description ?? 'No description provided.', 100) }}
                                </p>
                                <span class="item-status status-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                                @if($item->status !== 'claimed')
                                    <div style="margin-top: 1rem; text-align: center;">
                                        <a href="{{ auth()->check() ? route('claims.store', ['item' => $item->id]) : route('register') }}"
                                            class="btn btn-primary">
                                            <i class="fas fa-hand-point-right"></i>
                                            This is Mine!
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
                                <i class="fas fa-hand-holding"
                                    style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No found items reported yet. Help someone by reporting what you've found!</p>
                                <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-plus"></i>
                                    Report Found Item
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div id="claimed-content" class="tab-content">
                    <div class="items-grid" id="claimedItems">
                        @forelse($claimedItems as $item)
                            <div class="item-card">
                                <div class="item-image">
                                    @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    @else
                                        <i class="{{ getCategoryIcon($item->category) }}"></i>
                                    @endif
                                </div>
                                <h3 class="item-title">{{ $item->title }}</h3>
                                <div class="item-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $item->location }}</span>
                                    <span><i class="fas fa-calendar"></i>
                                        {{ $item->date_lost_found ? $item->date_lost_found->format('M d, Y') : $item->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="item-description">
                                    @if($item->type === 'lost')
                                        Lost item successfully reunited with owner! 🎉
                                    @else
                                        Found item successfully claimed by owner! 🎉
                                    @endif
                                    <br><small>{{ Str::limit($item->description ?? 'Another happy reunion through our platform!', 80) }}</small>
                                </p>
                                <span class="item-status status-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                                <div style="margin-top: 1rem; text-align: center;">
                                    <small style="color: #16a34a; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i>
                                        Successfully Reunited
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
                                <i class="fas fa-heart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No success stories yet. Be part of the first happy reunions!</p>
                                <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 1rem;">
                                    <i class="fas fa-plus"></i>
                                    Start Helping Others
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="cta-section fade-in-up">
                <h2>Ready to Make a Difference?</h2>
                <p>Join thousands of users helping reunite people with their lost belongings</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('register') }}" class="btn btn-primary"
                        style="font-size: 1.1rem; padding: 1rem 2rem;">
                        <i class="fas fa-user-plus"></i>
                        Create Free Account
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline"
                        style="font-size: 1.1rem; padding: 1rem 2rem; border-color: white; color: white;">
                        <i class="fas fa-sign-in-alt"></i>
                        Already Have Account?
                    </a>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Digital Lost & Found. Bringing communities together, one item at a time.</p>
        </div>
    </footer>

    <script>
        function showTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + '-content').classList.add('active');
        }

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const location = document.getElementById('locationFilter').value;

            // In a real app, this would make an AJAX call to your Laravel backend
            alert(`Searching for: "${searchTerm}" in category: "${category || 'All'}" at location: "${location || 'All'}"\n\nRegister to see full search results and contact item owners!`);

            // Redirect to register page after alert
            setTimeout(() => {
                window.location.href = "{{ route('register') }}";
            }, 2000);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Add search on enter key
            document.getElementById('searchInput').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Add some animation to stats (only if they have values > 0)
            const stats = document.querySelectorAll('.stat-number');
            stats.forEach(stat => {
                const target = parseInt(stat.textContent);
                if (target > 0) {
                    let current = 0;
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        stat.textContent = Math.floor(current);
                    }, 30);
                }
            });
        });

        // Add scroll effect for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>

</html>
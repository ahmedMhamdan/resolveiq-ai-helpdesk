<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResolveIQ | @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    @php
        $currentUser = auth()->user();
        $currentUserName = $currentUser?->name ?? 'Guest';
        $currentUserRole = ucfirst($currentUser?->role?->name ?? 'User');
        $isAdmin = strtolower($currentUser?->role?->name ?? '') === 'admin';
        $currentUserInitials = collect(explode(' ', $currentUserName))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'U';
    @endphp

    <div class="app">
        <aside class="sidebar">
            <a href="{{ route('dashboard') }}" class="brand">
                <div class="brand-mark">R</div>
                <div class="brand-text">Resolve<span>IQ</span></div>
            </a>

            <div class="nav-section">Overview</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">D</span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <span class="nav-icon">T</span>
                    <span>Tickets</span>
                </a>

                @if ($isAdmin)
                    <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <span class="nav-icon">DP</span>
                        <span>Departments</span>
                    </a>
                @else
                    <a href="#" class="is-disabled" aria-disabled="true" title="Admin only" onclick="return false;">
                        <span class="nav-icon">DP</span>
                        <span>Departments</span>
                        <span class="lock-icon">🔒</span>
                    </a>
                @endif

                @if ($isAdmin)
                    <a href="{{ route('agents.index') }}" class="{{ request()->routeIs('agents.*') ? 'active' : '' }}">
                        <span class="nav-icon">A</span>
                        <span>Agents</span>
                    </a>
                @else
                    <a href="#" class="is-disabled" aria-disabled="true" title="Admin only" onclick="return false;">
                        <span class="nav-icon">A</span>
                        <span>Agents</span>
                        <span class="lock-icon">🔒</span>
                    </a>
                @endif
            </nav>

            <div class="nav-section">AI Powered</div>
            <nav class="nav">
                <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}">
                    <span class="nav-icon">AI</span>
                    <span>AI Assistant</span>
                </a>

                @if ($isAdmin)
                    <a href="{{ route('knowledge.index') }}" class="{{ request()->routeIs('knowledge.*') ? 'active' : '' }}">
                        <span class="nav-icon">KB</span>
                        <span>Knowledge Base</span>
                    </a>
                @else
                    <a href="#" class="is-disabled" aria-disabled="true" title="Admin only" onclick="return false;">
                        <span class="nav-icon">KB</span>
                        <span>Knowledge Base</span>
                        <span class="lock-icon">🔒</span>
                    </a>
                @endif
            </nav>

            <div class="nav-section">System</div>
            <nav class="nav">
                <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙</span>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="sidebar-card">
                <h4>AI Helpdesk</h4>
                <p>Resolve tickets faster with summaries, suggested replies, and support insights.</p>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="search-wrap">
                    <span class="search-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="M20 20L16.65 16.65"></path>
                        </svg>
                    </span>

                    <input class="search" type="text" placeholder="Search tickets, users, or departments...">
                </div>

                <div class="top-actions">
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary new-ticket-btn">
                        + New Ticket
                    </a>

                    <button type="button" class="theme-switch" id="themeToggle" aria-label="Toggle theme">
                        <span class="theme-icon sun">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
                            </svg>
                        </span>

                        <span class="switch-track">
                            <span class="switch-thumb"></span>
                        </span>

                        <span class="theme-icon moon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                        </span>
                    </button>

                    <a href="{{ route('profile.show') }}" class="user-box">
                        <div class="avatar">{{ $currentUserInitials }}</div>
                        <div class="user-meta">
                            <strong>{{ $currentUserName }}</strong>
                            <span>{{ $currentUserRole }}</span>
                        </div>
                    </a>

                    <form action="{{ url('/logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="btn btn-secondary logout-btn">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="flash-message">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="flash-message flash-error">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        (() => {
            const root = document.documentElement;
            const toggle = document.getElementById('themeToggle');
            const savedTheme = localStorage.getItem('resolveiq-theme') || 'light';

            function applyTheme(theme) {
                root.setAttribute('data-theme', theme);
                document.body.classList.toggle('dark', theme === 'dark');
                localStorage.setItem('resolveiq-theme', theme);
            }

            applyTheme(savedTheme);

            toggle?.addEventListener('click', () => {
                const currentTheme = root.getAttribute('data-theme') || 'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

                applyTheme(nextTheme);
            });
        })();
    </script>
</body>
</html>

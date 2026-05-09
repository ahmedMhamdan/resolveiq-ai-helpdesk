<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResolveIQ | @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <a href="{{ url('/') }}" class="brand">
            <div class="brand-mark">R</div>
            <div class="brand-text">Resolve<span>IQ</span></div>
            </a>

            <div class="nav-section">Overview</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">D</span>
                    Dashboard
                </a>

                <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <span class="nav-icon">T</span>
                    Tickets
                </a>

                <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <span class="nav-icon">DP</span>
                <span>Departments</span>
                </a>

                <div class="disabled">
                    <span class="nav-icon">A</span>
                    Agents
                </div>
            </nav>

            <div class="nav-section">AI Powered</div>
            <nav class="nav">
                <div class="disabled">
                    <span class="nav-icon">AI</span>
                    AI Assistant
                </div>

                <div class="disabled">
                    <span class="nav-icon">KB</span>
                    Knowledge Base
                </div>
            </nav>

            <div class="nav-section">System</div>
            <nav class="nav">
                <div class="disabled">
                    <span class="nav-icon">S</span>
                    Settings
                </div>
            </nav>

            <div class="sidebar-card">
                <h4>AI Helpdesk</h4>
                <p>Resolve tickets faster with summaries, suggested replies, and support insights.</p>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="search-wrap">
                    <input class="search" type="text" placeholder="Search tickets, users, or departments...">
                    <span class="shortcut">Ctrl /</span>
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

                    <div class="user-box">
                        <div class="avatar">AM</div>
                        <div class="user-meta">
                            <strong>Ahmed M.</strong>
                            <span>Admin</span>
                        </div>
                    </div>
                </div>
            </div>
            @if (session('success'))
            <div class="flash-message">
                {{ session('success') }}
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

        if (toggle) {
            toggle.addEventListener('click', () => {
                const currentTheme = root.getAttribute('data-theme') || 'light';
                const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

                applyTheme(nextTheme);
            });
        }
    })();
</script>
</body>
</html>

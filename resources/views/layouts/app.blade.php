<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResolveIQ | @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>

    @php
    $currentUser = auth()->user();

    $unreadNotificationsCount = $currentUser?->unreadNotifications()->count() ?? 0;
    $latestNotifications = $currentUser
        ? $currentUser->unreadNotifications()->latest()->take(5)->get()
        : collect();

    $currentUserName = $currentUser?->name ?? 'Guest';
    $currentUserRoleName = strtolower($currentUser?->role?->name ?? 'user');
    $currentUserRole = ucfirst($currentUserRoleName);

    $isAdmin = $currentUserRoleName === 'admin';
    $isAgent = $currentUserRoleName === 'agent';
    $isUser = $currentUserRoleName === 'user';

    $currentUserInitials = collect(explode(' ', $currentUserName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'U';

    $currentUserAvatarUrl = null;

    if ($currentUser?->avatar_path) {
        $avatarPath = ltrim($currentUser->avatar_path, '/');

        if (str_starts_with($avatarPath, 'http://') || str_starts_with($avatarPath, 'https://')) {
            $currentUserAvatarUrl = $avatarPath;
        } elseif (str_starts_with($avatarPath, 'images/') || str_starts_with($avatarPath, 'storage/')) {
            $currentUserAvatarUrl = asset($avatarPath);
        } else {
            $currentUserAvatarUrl = asset('storage/' . $avatarPath);
        }
    }
    @endphp

    <div class="app">
        <aside class="sidebar" id="appSidebar" aria-label="Main navigation">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="brand">
                    <div class="brand-mark">R</div>
                    <div class="brand-text">Resolve<span>IQ</span></div>
                </a>

                <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">
                    &times;
                </button>
            </div>

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

                    <a href="{{ route('agents.index') }}" class="{{ request()->routeIs('agents.*') ? 'active' : '' }}">
                        <span class="nav-icon">A</span>
                        <span>Agents</span>
                    </a>
                @endif
                @if (strtolower(auth()->user()->role?->name ?? '') === 'admin')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="nav-icon">U</span>
                    Users
                </a>
            @endif
            </nav>

            @if ($isAdmin || $isAgent)
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
                    @endif
                </nav>
            @endif

            <div class="nav-section">Account</div>
            <nav class="nav">
                <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="nav-icon">P</span>
                    <span>Profile</span>
                </a>
            </nav>

            @if ($isAdmin || $isAgent)
                <div class="sidebar-card">
                    <h4>AI Helpdesk</h4>
                    <p>Resolve tickets faster with summaries, suggested replies, and support insights.</p>
                </div>
            @endif


        </aside>

        <main class="main">
            <div class="topbar">
                <button
                    type="button"
                    class="mobile-sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="Open menu"
                    aria-controls="appSidebar"
                    aria-expanded="false"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <form class="search-wrap topbar-search-form" method="GET" action="{{ route('tickets.index') }}" role="search">
                    <span class="search-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="M20 20L16.65 16.65"></path>
                        </svg>
                    </span>

                    <input
                        class="search"
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ $isAdmin ? 'Search all tickets...' : ($isAgent ? 'Search assigned tickets...' : 'Search your tickets...') }}"
                        autocomplete="off"
                    >
                </form>

            <div class="mobile-quick-actions" aria-label="Mobile quick actions">
                @if ($isAdmin || $isUser)
                    <a href="{{ route('tickets.create') }}" class="mobile-action-pill mobile-action-primary">
                        + Ticket
                    </a>
                @endif

                <a
                    href="{{ route('profile.show') }}"
                    class="mobile-action-pill mobile-profile-pill"
                    title="{{ $currentUserName }}"
                >
                    <span class="mobile-profile-avatar">
                        @if ($currentUserAvatarUrl)
                            <img
                                src="{{ $currentUserAvatarUrl }}"
                                alt="{{ $currentUserName }} avatar"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                            >
                            <span class="avatar-fallback" style="display: none;">?</span>
                        @else
                            <span class="avatar-fallback">?</span>
                        @endif
                    </span>
                    <span>{{ $currentUserName }}</span>
                </a>

                <button
                    type="button"
                    class="mobile-action-pill mobile-theme-pill js-theme-toggle"
                    id="mobileThemeToggle"
                    aria-label="Toggle theme"
                    aria-pressed="false"
                    title="Toggle theme"
                >
                    <svg class="mobile-theme-svg mobile-theme-sun" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"></circle>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                    </svg>

                    <svg class="mobile-theme-svg mobile-theme-moon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <form action="{{ url('/logout') }}" method="POST" class="mobile-logout-inline-form">
                    @csrf
                    <button type="submit" class="mobile-action-pill mobile-logout-pill">
                        Logout
                    </button>
                </form>
            </div>

                <div class="top-actions">
                    @if ($isAdmin || $isUser)
                    <a href="{{ route('tickets.create') }}" class="btn new-ticket-btn">
                        + New Ticket
                    </a>
                    @endif
                    <div class="notifications-dropdown" id="notificationsDropdown">
                        <button
                            type="button"
                            class="top-notification-btn {{ $unreadNotificationsCount > 0 ? 'has-unread' : '' }}"
                            id="notificationsToggle"
                            aria-label="Notifications"
                            title="Notifications"
                        >
                            <svg
                                class="notification-svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                <path d="M10 20a2 2 0 0 0 4 0" />
                            </svg>

                            @if ($unreadNotificationsCount > 0)
                                <strong>{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</strong>
                            @endif
                        </button>

                        <div class="notifications-menu" id="notificationsMenu">
                            <div class="notifications-menu-head">
                                <div>
                                    <h4>Notifications</h4>
                                    <span>{{ $unreadNotificationsCount }} unread</span>
                                </div>

                                <a href="{{ route('notifications.index') }}">View all</a>
                            </div>

                            <div class="notifications-menu-list">
                                @forelse ($latestNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $isUnread = is_null($notification->read_at);
                                    @endphp

                                    <form
                                        action="{{ route('notifications.read', $notification) }}"
                                        method="POST"
                                        class="notification-mini-item {{ $isUnread ? 'unread' : '' }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit">
                                            <span class="notification-mini-icon">
                                                {{ strtoupper(substr($data['type'] ?? 'T', 0, 1)) }}
                                            </span>

                                            <span class="notification-mini-content">
                                                <strong>{{ $data['title'] ?? 'Notification' }}</strong>
                                                <small>{{ $data['message'] ?? '' }}</small>
                                                <em>{{ $notification->created_at?->diffForHumans() }}</em>
                                            </span>
                                        </button>
                                    </form>
                                @empty
                                    <div class="notification-mini-empty">
                                        No unread notifications.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <button type="button" class="theme-switch js-theme-toggle" id="themeToggle" aria-label="Toggle theme">
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
                        <div class="avatar">
                            @if ($currentUserAvatarUrl)
                                <img
                                    src="{{ $currentUserAvatarUrl }}"
                                    alt="{{ $currentUserName }} avatar"
                                    class="topbar-avatar-img"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                                >
                                <span class="avatar-fallback" style="display: none;">?</span>
                            @else
                                <span class="avatar-fallback">?</span>
                            @endif
                        </div>
                        <div class="user-meta">
                            <strong>{{ $currentUserName }}</strong>
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
            const themeToggles = document.querySelectorAll('.js-theme-toggle');
            const savedTheme = localStorage.getItem('resolveiq-theme') || 'light';

            function applyTheme(theme) {
                root.setAttribute('data-theme', theme);
                document.body.classList.toggle('dark', theme === 'dark');
                localStorage.setItem('resolveiq-theme', theme);

                themeToggles.forEach(button => {
                    button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                    button.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                    button.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                });
            }

            applyTheme(savedTheme);

            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const sidebar = document.getElementById('appSidebar');

            function openSidebar() {
                document.body.classList.add('sidebar-open');
                sidebarBackdrop?.removeAttribute('hidden');
                sidebarToggle?.setAttribute('aria-expanded', 'true');
            }

            function closeSidebar() {
                document.body.classList.remove('sidebar-open');
                sidebarBackdrop?.setAttribute('hidden', '');
                sidebarToggle?.setAttribute('aria-expanded', 'false');
            }

            sidebarToggle?.addEventListener('click', event => {
                event.stopPropagation();
                if (document.body.classList.contains('sidebar-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarBackdrop?.addEventListener('click', closeSidebar);

            sidebar?.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.matchMedia('(max-width: 860px)').matches) {
                        closeSidebar();
                    }
                });
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            themeToggles.forEach(button => {
                button.addEventListener('click', () => {
                    const currentTheme = root.getAttribute('data-theme') || 'light';
                    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    applyTheme(nextTheme);
                });
            });

            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const notificationsToggle = document.getElementById('notificationsToggle');

            notificationsToggle?.addEventListener('click', event => {
                event.stopPropagation();
                notificationsDropdown?.classList.toggle('open');
            });

            document.addEventListener('click', event => {
                if (! notificationsDropdown?.contains(event.target)) {
                    notificationsDropdown?.classList.remove('open');
                }
            });
        })();
    </script>
</body>
</html>

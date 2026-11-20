@php
    $acceptLanguage = strtolower(request()->server('HTTP_ACCEPT_LANGUAGE', ''));
    $browserLocale = str_starts_with($acceptLanguage, 'ar') ? 'ar' : 'en';
    $currentLocale = session('locale', auth()->user()?->locale ?: $browserLocale);

    if (! in_array($currentLocale, ['en', 'ar'], true)) {
        $currentLocale = 'en';
    }

    app()->setLocale($currentLocale);

    $nextLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $isArabicLocale = $currentLocale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $isArabicLocale ? 'rtl' : 'ltr' }}" data-theme="light" data-locale="{{ $currentLocale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ResolveIQ | @yield('title', 'Dashboard')</title>
    @include('partials.head-assets')
</head>
<body class="app-page">
    <a class="skip-link" href="#main-content">{{ $isArabicLocale ? '????? ??? ???????' : 'Skip to content' }}</a>
    <div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>

    @php
    $currentUser = auth()->user();

    $unreadNotificationsCount = $currentUser?->unreadNotifications()->count() ?? 0;
    $latestNotifications = $currentUser
        ? $currentUser->unreadNotifications()->latest()->take(5)->get()
        : collect();

    $currentUserName = $currentUser?->name ?? 'Guest';
    $currentUserRoleName = $currentUser?->roleName() ?? 'user';
    $currentUserRole = ucfirst($currentUserRoleName);

    $isAdmin = $currentUserRoleName === 'admin';
    $isAgent = $currentUserRoleName === 'agent';
    $isUser = $currentUserRoleName === 'user';

    $currentUserInitials = collect(explode(' ', $currentUserName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'U';

    $currentUserAvatarUrl = $currentUser?->avatarUrl();
    @endphp

    <div class="app">
        <aside class="sidebar" id="appSidebar" aria-label="Main navigation">
            <div class="sidebar-header">
                <x-brand :href="route('dashboard')" />

                <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">
                    <x-icon name="close" />
                </button>
            </div>

            <div class="nav-section" data-auto-translate>Overview</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <x-icon name="dashboard" class="nav-icon" />
                    <span data-auto-translate>Dashboard</span>
                </a>

                <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}" @if(request()->routeIs('tickets.*')) aria-current="page" @endif>
                    <x-icon name="tickets" class="nav-icon" />
                    <span data-auto-translate>Tickets</span>
                </a>

                @if ($isAdmin)
                    <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}" @if(request()->routeIs('departments.*')) aria-current="page" @endif>
                        <x-icon name="departments" class="nav-icon" />
                        <span data-auto-translate>Departments</span>
                    </a>

                    <a href="{{ route('agents.index') }}" class="{{ request()->routeIs('agents.*') ? 'active' : '' }}" @if(request()->routeIs('agents.*')) aria-current="page" @endif>
                        <x-icon name="agents" class="nav-icon" />
                        <span data-auto-translate>Agents</span>
                    </a>
                @endif
                @if ($isAdmin)
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}" @if(request()->routeIs('users.*')) aria-current="page" @endif>
                    <x-icon name="users" class="nav-icon" />
                    <span data-auto-translate>Users</span>
                </a>
            @endif
            </nav>

            @if ($isAdmin || $isAgent)
                <div class="nav-section" data-auto-translate>AI Powered</div>
                <nav class="nav">
                    <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}" @if(request()->routeIs('ai.*')) aria-current="page" @endif>
                        <x-icon name="spark" class="nav-icon" />
                        <span data-auto-translate>AI Assistant</span>
                    </a>

                    @if ($isAdmin)
                        <a href="{{ route('knowledge.index') }}" class="{{ request()->routeIs('knowledge.*') ? 'active' : '' }}" @if(request()->routeIs('knowledge.*')) aria-current="page" @endif>
                            <x-icon name="book" class="nav-icon" />
                            <span data-auto-translate>Knowledge Base</span>
                        </a>
                    @endif
                </nav>
            @endif

            <div class="nav-section" data-auto-translate>Account</div>
            <nav class="nav">
                <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                    <x-icon name="profile" class="nav-icon" />
                    <span data-auto-translate>Profile</span>
                </a>
            </nav>



        </aside>

        <main class="main" id="main-content">
            <div class="topbar">
                <button
                    type="button"
                    class="mobile-sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="Open menu"
                    aria-controls="appSidebar"
                    aria-expanded="false"
                >
                    <x-icon name="menu" />
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
                        value="{{ request()->string('search', strict: true) }}"
                        aria-label="{{ __('dashboard.search_placeholder') }}"
                        placeholder="{{ $isAdmin ? 'Search all tickets...' : ($isAgent ? 'Search assigned tickets...' : 'Search your tickets...') }}"
                        data-auto-translate-attribute="placeholder"
                        data-auto-translate-original="{{ $isAdmin ? 'Search all tickets...' : ($isAgent ? 'Search assigned tickets...' : 'Search your tickets...') }}"
                        autocomplete="off"
                    >
                </form>

                <div class="mobile-toolbar" role="group" aria-label="{{ $isArabicLocale ? '??????? ?????' : 'Quick actions' }}">
                    @if ($isAdmin || $isUser)
                        <a href="{{ route('tickets.create') }}" class="toolbar-button toolbar-create">
                            <span data-auto-translate>+ Ticket</span>
                        </a>
                    @endif

                    <a
                        href="{{ route('profile.show') }}"
                        class="toolbar-button toolbar-profile"
                        title="{{ $currentUserName }}"
                        aria-label="{{ __('profile.title') }}"
                    >
                        <span class="toolbar-avatar">
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
                    </a>

                    <x-theme-toggle class="toolbar-button toolbar-theme" id="mobileThemeToggle" />


                    <form action="{{ route('translations.switch', $nextLocale) }}" method="POST" class="toolbar-language-form">
                        @csrf
                        <button
                            type="submit"
                            class="toolbar-button toolbar-language"
                            aria-label="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                            title="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                        >
                            <span class="language-switch-code" data-no-auto-translate>{{ strtoupper($nextLocale) }}</span>
                        </button>
                    </form>

                    <form action="{{ url('/logout') }}" method="POST" class="toolbar-logout-form">
                        @csrf
                        <button type="submit" class="toolbar-button toolbar-logout" aria-label="{{ $isArabicLocale ? 'تسجيل الخروج' : 'Logout' }}">
                            <x-icon name="logout" />
                        </button>
                    </form>
                </div>

                <div class="top-actions">
                    @if ($isAdmin || $isUser)
                    <a href="{{ route('tickets.create') }}" class="btn new-ticket-btn">
                        <span data-auto-translate>+ New Ticket</span>
                    </a>
                    @endif
                    <div class="notifications-dropdown" id="notificationsDropdown">
                        <button
                            type="button"
                            class="top-notification-btn {{ $unreadNotificationsCount > 0 ? 'has-unread' : '' }}"
                            id="notificationsToggle"
                            aria-expanded="false"
                            aria-controls="notificationsMenu"
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
                                    <h4 data-auto-translate>Notifications</h4>
                                    <span><span>{{ $unreadNotificationsCount }}</span> <span data-auto-translate>unread</span></span>
                                </div>

                                <a href="{{ route('notifications.index') }}" data-auto-translate>View all</a>
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
                                        <span data-auto-translate>No unread notifications.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('translations.switch', $nextLocale) }}" method="POST" class="language-switch-form">
                        @csrf
                        <button
                            type="submit"
                            class="language-switch"
                            aria-label="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                            title="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                        >
                            <span class="language-switch-code" data-no-auto-translate>{{ strtoupper($nextLocale) }}</span>
                        </button>
                    </form>

                    <x-theme-toggle id="themeToggle" />

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
                            <span data-auto-translate>Logout</span>
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

</body>
</html>

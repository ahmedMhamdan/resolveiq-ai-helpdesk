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
    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}?v={{ filemtime(public_path('css/resolveiq.css')) }}">
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

            <div class="nav-section" data-auto-translate>Overview</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">D</span>
                    <span data-auto-translate>Dashboard</span>
                </a>

                <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <span class="nav-icon">T</span>
                    <span data-auto-translate>Tickets</span>
                </a>

                @if ($isAdmin)
                    <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <span class="nav-icon">DP</span>
                        <span data-auto-translate>Departments</span>
                    </a>

                    <a href="{{ route('agents.index') }}" class="{{ request()->routeIs('agents.*') ? 'active' : '' }}">
                        <span class="nav-icon">A</span>
                        <span data-auto-translate>Agents</span>
                    </a>
                @endif
                @if (strtolower(auth()->user()->role?->name ?? '') === 'admin')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="nav-icon">U</span>
                    <span data-auto-translate>Users</span>
                </a>
            @endif
            </nav>

            @if ($isAdmin || $isAgent)
                <div class="nav-section" data-auto-translate>AI Powered</div>
                <nav class="nav">
                    <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}">
                        <span class="nav-icon">AI</span>
                        <span data-auto-translate>AI Assistant</span>
                    </a>

                    @if ($isAdmin)
                        <a href="{{ route('knowledge.index') }}" class="{{ request()->routeIs('knowledge.*') ? 'active' : '' }}">
                            <span class="nav-icon">KB</span>
                            <span data-auto-translate>Knowledge Base</span>
                        </a>
                    @endif
                </nav>
            @endif

            <div class="nav-section" data-auto-translate>Account</div>
            <nav class="nav">
                <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="nav-icon">P</span>
                    <span data-auto-translate>Profile</span>
                </a>
            </nav>

            @if ($isAdmin || $isAgent)
                <div class="sidebar-card">
                    <h4 data-auto-translate>AI Helpdesk</h4>
                    <p data-auto-translate>Resolve tickets faster with summaries, suggested replies, and support insights.</p>
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
                        data-auto-translate-attribute="placeholder"
                        data-auto-translate-original="{{ $isAdmin ? 'Search all tickets...' : ($isAgent ? 'Search assigned tickets...' : 'Search your tickets...') }}"
                        autocomplete="off"
                    >
                </form>

            <div class="mobile-quick-actions" aria-label="Mobile quick actions">
                @if ($isAdmin || $isUser)
                    <a href="{{ route('tickets.create') }}" class="mobile-action-pill mobile-action-primary">
                        <span data-auto-translate>+ Ticket</span>
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


                <form action="{{ route('translations.switch', $nextLocale) }}" method="POST" class="mobile-language-form">
                    @csrf
                    <button
                        type="submit"
                        class="mobile-action-pill mobile-language-pill"
                        aria-label="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                        title="{{ $isArabicLocale ? 'Switch to English' : 'Switch to Arabic' }}"
                    >
                        <span class="language-switch-code">{{ strtoupper($nextLocale) }}</span>
                    </button>
                </form>

                <form action="{{ url('/logout') }}" method="POST" class="mobile-logout-inline-form">
                    @csrf
                    <button type="submit" class="mobile-action-pill mobile-logout-pill">
                        <span data-auto-translate>Logout</span>
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
                            <span class="language-switch-code">{{ strtoupper($nextLocale) }}</span>
                        </button>
                    </form>

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


            const currentLocale = @json($currentLocale);
            const translationEndpoint = @json(route('translations.ui'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const builtinArabicTranslations = {
                '+ New Ticket': '+ تذكرة',
                '+ Ticket': '+ تذكرة',
                'Account': 'الحساب',
                'Active requests': 'طلبات نشطة',
                'Agents': 'الوكلاء',
                'AI Assistant': 'المساعد الذكي',
                'AI Helpdesk': 'مكتب الدعم الذكي',
                'AI Powered': 'مدعوم بالذكاء الاصطناعي',
                'Dashboard': 'لوحة التحكم',
                'Department': 'القسم',
                'Departments': 'الأقسام',
                'Due Date': 'تاريخ الاستحقاق',
                'High': 'عالية',
                'Knowledge Base': 'قاعدة المعرفة',
                'Latest Tickets': 'أحدث التذاكر',
                'Logout': 'تسجيل الخروج',
                'Needs attention': 'تحتاج متابعة',
                'New support requests in the workspace.': 'أحدث طلبات الدعم في مساحة العمل.',
                'Notifications': 'الإشعارات',
                'Open Tickets': 'التذاكر المفتوحة',
                'Overview': 'نظرة عامة',
                'Overview of support performance, ticket volume, and urgent issues.': 'نظرة على أداء الدعم وحجم التذاكر والمشكلات العاجلة.',
                'Pending': 'قيد الانتظار',
                'Priority': 'الأولوية',
                'Profile': 'الملف الشخصي',
                'Requester': 'مقدم الطلب',
                'Resolved tickets': 'تذاكر محلولة',
                'Resolve tickets faster with summaries, suggested replies, and support insights.': 'حل التذاكر بسرعة أكبر باستخدام الملخصات والردود المقترحة ورؤى الدعم.',
                'Search': 'بحث',
                'Search all tickets...': 'ابحث في كل التذاكر...',
                'Search assigned tickets...': 'ابحث في التذاكر المعينة...',
                'Search tickets...': 'ابحث في التذاكر...',
                'Search your tickets...': 'ابحث في تذاكرك...',
                'Solved': 'تم الحل',
                'Status': 'الحالة',
                'Ticket': 'التذكرة',
                'Tickets': 'التذاكر',
                'Updated': 'آخر تحديث',
                'Urgent': 'عاجلة',
                'Users': 'المستخدمون',
                'View All': 'عرض الكل',
                'View all': 'عرض الكل',
                'View Tickets': 'عرض التذاكر',
                'Waiting for updates': 'بانتظار التحديثات',
            };

            const builtinArabicInstantTranslations = {
                'Billing': 'الفوترة',
                'Billing page shows blank screen': 'صفحة الفوترة تعرض شاشة فارغة',
                '+ New Agent': '+ وكيل',
                '+ New Department': '+ قسم',
                'Actions': 'الإجراءات',
                'Activity': 'نشاط',
                'Activity Details': 'تفاصيل النشاط',
                'Active': 'نشط',
                'Add Reply': 'إضافة رد',
                'Account Details': 'تفاصيل الحساب',
                'Account Info': 'معلومات الحساب',
                'Account Information': 'معلومات الحساب',
                'Accounts': 'الحسابات',
                'Admin': 'مدير',
                'Agent Dashboard': 'لوحة الوكيل',
                'Agent Name': 'اسم الوكيل',
                'Article': 'المقال',
                'Article Title': 'عنوان المقال',
                'Article writing guide': 'دليل كتابة المقال',
                'Articles': 'المقالات',
                'Assistant Workspace': 'مساحة عمل المساعد',
                'Author': 'الكاتب',
                'AI Output': 'نتيجة الذكاء الاصطناعي',
                'AI Result': 'نتيجة الذكاء الاصطناعي',
                'All available support departments.': 'كل أقسام الدعم المتاحة.',
                'All support agents in the workspace.': 'كل وكلاء الدعم في مساحة العمل.',
                'Assigned Tickets': 'التذاكر المعينة',
                'Assign Agent': 'تعيين وكيل',
                'Assign': 'تعيين',
                'Back to Ticket': 'العودة إلى التذكرة',
                'Back to Tickets': 'العودة إلى التذاكر',
                'Back': 'رجوع',
                'Change Role': 'تغيير الدور',
                'Changed by': 'تم التغيير بواسطة',
                'Closed': 'مغلقة',
                'Close Ticket': 'إغلاق التذكرة',
                'Close': 'إغلاق',
                'Create a new support request and assign it to the right department.': 'أنشئ طلب دعم جديدا وعيّنه للقسم المناسب.',
                'Create a new support request and our team will review it.': 'أنشئ طلب دعم جديدا وسيراجعه فريقنا.',
                'Create a department for ticket routing.': 'أنشئ قسما لتوجيه التذاكر.',
                'Create a new support agent account.': 'أنشئ حساب وكيل دعم جديد.',
                'Create a reusable support article for agents and AI-assisted replies.': 'أنشئ مقالا قابلا لإعادة الاستخدام للوكلاء والردود المدعومة بالذكاء الاصطناعي.',
                'Create Agent': 'إنشاء وكيل',
                'Create Article': 'إنشاء مقال',
                'Create Department': 'إنشاء قسم',
                'Create short articles for repeated customer problems.': 'أنشئ مقالات قصيرة للمشكلات المتكررة للعملاء.',
                'Create Ticket': 'إنشاء تذكرة',
                'Create a structured internal summary for the support team.': 'أنشئ ملخصا داخليا منظما لفريق الدعم.',
                'Custom': 'مخصص',
                'Custom Instruction': 'تعليمات مخصصة',
                'Create your first support agent to start assigning tickets.': 'أنشئ أول وكيل دعم لبدء تعيين التذاكر.',
                'Create your first article to start building the support knowledge library.': 'أنشئ أول مقال لبدء بناء مكتبة معرفة الدعم.',
                'Created Tickets': 'التذاكر المنشأة',
                'Created': 'تاريخ الإنشاء',
                'Current Account': 'الحساب الحالي',
                'Deleted Tickets': 'التذاكر المحذوفة',
                'Delete Forever': 'حذف نهائي',
                'Delete': 'حذف',
                'Deleted At': 'تاريخ الحذف',
                'Description': 'الوصف',
                'Department Name': 'اسم القسم',
                'Edit Agent': 'تعديل الوكيل',
                'Edit Article': 'تعديل المقال',
                'Edit Department': 'تعديل القسم',
                'Edit Profile': 'تعديل الملف الشخصي',
                'Edit User': 'تعديل المستخدم',
                'Email': 'البريد الإلكتروني',
                'Example: How to reset 2FA': 'مثال: كيفية إعادة ضبط المصادقة الثنائية',
                'Example: Support Agent': 'مثال: وكيل دعم',
                'Example: Technical Support': 'مثال: الدعم الفني',
                'From': 'من',
                'Generate AI summary, reply, or priority suggestion for this ticket.': 'أنشئ ملخصا أو ردا أو اقتراح أولوية لهذه التذكرة بالذكاء الاصطناعي.',
                'Generate Summary': 'إنشاء ملخص',
                'Generate': 'إنشاء',
                'Help agents': 'مساعدة الوكلاء',
                'Ask anything specific about this ticket using your own instruction.': 'اطلب أي شيء محدد عن هذه التذكرة باستخدام تعليماتك الخاصة.',
                'Improve AI replies': 'تحسين ردود الذكاء الاصطناعي',
                'Instead of writing the same answer many times, we save common solutions here, such as password reset steps, login problems, account verification, or troubleshooting instructions. Later, the AI Assistant can use published articles from this library to generate more accurate replies based on real helpdesk content': 'بدلا من كتابة نفس الإجابة مرات كثيرة، نحفظ الحلول الشائعة هنا مثل خطوات إعادة تعيين كلمة المرور، ومشكلات تسجيل الدخول، وتحقق الحساب، وتعليمات استكشاف الأخطاء. لاحقا يستطيع المساعد الذكي استخدام المقالات المنشورة من هذه المكتبة لإنشاء ردود أدق مبنية على محتوى دعم حقيقي',
                'Internal note': 'ملاحظة داخلية',
                'Instructions (optional, required for Custom)': 'التعليمات (اختيارية، مطلوبة للمخصص)',
                'Latest replies written by this account.': 'أحدث الردود التي كتبها هذا الحساب.',
                'Latest Replies': 'أحدث الردود',
                'Latest support requests created by you.': 'أحدث طلبات الدعم التي أنشأتها.',
                'Latest system activity for this account.': 'أحدث نشاط للنظام لهذا الحساب.',
                'Latest ticket updates and workspace actions.': 'أحدث تحديثات التذاكر وإجراءات مساحة العمل.',
                'Latest tickets assigned to this user.': 'أحدث التذاكر المعينة لهذا المستخدم.',
                'Latest tickets assigned to you.': 'أحدث التذاكر المعينة لك.',
                'Latest tickets created by this user.': 'أحدث التذاكر التي أنشأها هذا المستخدم.',
                'Latest updates related to your tickets.': 'أحدث التحديثات المتعلقة بتذاكرك.',
                'Last Updated': 'آخر تحديث',
                'Leave empty to keep current password': 'اتركه فارغا للإبقاء على كلمة المرور الحالية',
                'Make User': 'تحويل إلى مستخدم',
                'Manage': 'إدارة',
                'Manage support agents who handle tickets.': 'إدارة وكلاء الدعم الذين يتعاملون مع التذاكر.',
                'Manage support departments used for ticket routing.': 'إدارة أقسام الدعم المستخدمة لتوجيه التذاكر.',
                'Manage reusable support articles and internal help content': 'إدارة مقالات الدعم القابلة لإعادة الاستخدام ومحتوى المساعدة الداخلي',
                'Manage customer accounts, review user activity, and promote users to agents when needed': 'إدارة حسابات العملاء، ومراجعة نشاط المستخدمين، وترقية المستخدمين إلى وكلاء عند الحاجة',
                'Manage your account information and workspace activity': 'إدارة معلومات حسابك ونشاط مساحة العمل',
                'Member Since': 'عضو منذ',
                'New Article +': '+ مقال',
                '+ New Article': '+ مقال',
                'Invoice amount is incorrect': 'مبلغ الفاتورة غير صحيح',
                'Low': 'منخفضة',
                'Medium': 'متوسطة',
                'Need help resetting 2FA': 'أحتاج مساعدة في إعادة ضبط المصادقة الثنائية',
                'Needs Attention': 'تحتاج متابعة',
                'New Agent': 'وكيل جديد',
                'New Department': 'قسم جديد',
                'No agents found.': 'لا يوجد وكلاء.',
                'No department': 'لا يوجد قسم',
                'No departments found.': 'لا توجد أقسام.',
                'No description': 'لا يوجد وصف',
                'No deleted tickets found.': 'لا توجد تذاكر محذوفة.',
                'No email': 'لا يوجد بريد إلكتروني',
                'No activity logs matched your search.': 'لا توجد سجلات نشاط مطابقة للبحث.',
                'No assigned tickets yet.': 'لا توجد تذاكر معينة بعد.',
                'No created tickets yet.': 'لا توجد تذاكر منشأة بعد.',
                'No matching deleted tickets found.': 'لا توجد تذاكر محذوفة مطابقة.',
                'No matching overdue tickets found.': 'لا توجد تذاكر متأخرة مطابقة.',
                'No matching unassigned tickets found.': 'لا توجد تذاكر غير معينة مطابقة.',
                'No recent activity yet.': 'لا يوجد نشاط حديث بعد.',
                'No overdue tickets found.': 'لا توجد تذاكر متأخرة.',
                'No tickets found.': 'لا توجد تذاكر.',
                'No unassigned tickets found.': 'لا توجد تذاكر غير معينة.',
                'No users found.': 'لا يوجد مستخدمون.',
                'Not set': 'غير محدد',
                'Newest support requests in the workspace.': 'أحدث طلبات الدعم في مساحة العمل.',
                'No AI output yet': 'لا توجد نتيجة ذكاء اصطناعي بعد',
                'Optional: adjust tone, language, length, or ask a specific question about this ticket...': 'اختياري: عدّل النبرة أو اللغة أو الطول، أو اطرح سؤالا محددا عن هذه التذكرة...',
                'My Dashboard': 'لوحتي',
                'My Tickets': 'تذاكري',
                'Open AI Assistant': 'فتح المساعد الذكي',
                'Open Ticket': 'فتح التذكرة',
                'Open': 'مفتوحة',
                'Optional: make the summary shorter, write it in Arabic, or include a specific field like due date': 'اختياري: اجعل الملخص أقصر، أو اكتبه بالعربية، أو أضف حقلا محددا مثل تاريخ الاستحقاق',
                'Overdue Tickets': 'التذاكر المتأخرة',
                'Overdue': 'متأخرة',
                'Password reset email not received': 'لم يتم استلام بريد إعادة تعيين كلمة المرور',
                'Previous value': 'القيمة السابقة',
                'Profile picture': 'صورة الملف الشخصي',
                'Protected Admin': 'مدير محمي',
                'Promote normal users to agents. Agents are managed from the Agents page.': 'رقّ المستخدمين العاديين إلى وكلاء. تتم إدارة الوكلاء من صفحة الوكلاء.',
                'Published articles can be sent as extra context to the AI Assistant.': 'يمكن إرسال المقالات المنشورة كسياق إضافي إلى المساعد الذكي.',
                'Recent Activity': 'النشاط الأخير',
                'Recent replies written by this user.': 'أحدث الردود التي كتبها هذا المستخدم.',
                'Recent Replies': 'الردود الأخيرة',
                'Reply': 'رد',
                'Replies': 'الردود',
                'Repeat new password': 'كرر كلمة المرور الجديدة',
                'Repeat the new password': 'كرر كلمة المرور الجديدة',
                'Reusable support content for agents and AI-assisted replies.': 'محتوى دعم قابل لإعادة الاستخدام للوكلاء والردود المدعومة بالذكاء الاصطناعي.',
                'Restore deleted tickets or permanently remove them.': 'استعد التذاكر المحذوفة أو احذفها نهائيا.',
                'Restore': 'استعادة',
                'Review new tickets and assign them to support agents.': 'راجع التذاكر الجديدة وعيّنها لوكلاء الدعم.',
                'Review tickets that passed their due date and still need action.': 'راجع التذاكر التي تجاوزت تاريخ الاستحقاق وما زالت تحتاج إجراء.',
                'Reset': 'إعادة ضبط',
                'Security': 'الأمان',
                'Search deleted ticket...': 'ابحث في التذاكر المحذوفة...',
                'Search logs...': 'ابحث في السجلات...',
                'Search name or email...': 'ابحث بالاسم أو البريد الإلكتروني...',
                'Search overdue ticket...': 'ابحث في التذاكر المتأخرة...',
                'Search ticket...': 'ابحث في التذاكر...',
                'Search articles...': 'ابحث في المقالات...',
                'Select due date': 'اختر تاريخ الاستحقاق',
                'Select agent': 'اختر وكيلا',
                'Select ticket': 'اختر تذكرة',
                'Select a ticket, choose an action, then generate.': 'اختر تذكرة، ثم اختر إجراء، ثم أنشئ النتيجة.',
                'Send Reply': 'إرسال الرد',
                'Short description about this department...': 'وصف قصير لهذا القسم...',
                'Showing': 'عرض',
                'Store solutions': 'تخزين الحلول',
                'Suggest Priority': 'اقتراح الأولوية',
                'Suggest Due Date': 'اقتراح تاريخ الاستحقاق',
                'Suggest Reply': 'اقتراح رد',
                'Summary': 'ملخص',
                'Draft a clear customer-facing support response.': 'اكتب ردا واضحا موجها للعميل.',
                'Support Agent': 'وكيل دعم',
                'System': 'النظام',
                'Technical Support': 'الدعم الفني',
                'TICKETS': 'التذاكر',
                'This will permanently delete the ticket. Continue?': 'سيتم حذف التذكرة نهائيا. هل تريد المتابعة؟',
                'Ticket removed': 'تمت إزالة التذكرة',
                'Ticket removed': 'تمت إزالة التذكرة',
                'Time': 'الوقت',
                'Tickets moved to archive by soft delete.': 'تذاكر نُقلت إلى الأرشيف بالحذف المرن.',
                'to': 'إلى',
                'To': 'إلى',
                'Track your support tickets and recent request updates.': 'تابع تذاكر الدعم الخاصة بك وآخر تحديثات الطلبات.',
                'Unable to login to account': 'تعذر تسجيل الدخول إلى الحساب',
                'Unassigned Tickets': 'تذاكر غير معينة',
                'Unassigned': 'غير معين',
                'Unknown': 'غير معروف',
                'User': 'مستخدم',
                'Update account information and reset password when needed.': 'حدّث معلومات الحساب وأعد ضبط كلمة المرور عند الحاجة.',
                'Update assignment, status, priority, and due date.': 'حدّث التعيين والحالة والأولوية وتاريخ الاستحقاق.',
                'Update agent account information and profile picture.': 'حدّث معلومات حساب الوكيل وصورة الملف الشخصي.',
                'Update department information.': 'حدّث معلومات القسم.',
                'Update Profile': 'تحديث الملف الشخصي',
                'Update support knowledge content used by agents and AI-assisted replies.': 'حدّث محتوى المعرفة الذي يستخدمه الوكلاء والردود المدعومة بالذكاء الاصطناعي.',
                'Update Ticket': 'تحديث التذكرة',
                'Update User': 'تحديث المستخدم',
                'Update your account information and profile picture.': 'حدّث معلومات حسابك وصورة ملفك الشخصي.',
                'Update': 'تحديث',
                'User Avatar': 'صورة المستخدم',
                'User Details': 'تفاصيل المستخدم',
                'User Name': 'اسم المستخدم',
                'Users Management': 'إدارة المستخدمين',
                'View': 'عرض',
                'View details': 'عرض التفاصيل',
                'Waiting for Assignment': 'بانتظار التعيين',
                'What is the Knowledge Base?': 'ما هي قاعدة المعرفة؟',
                'Write a reply...': 'اكتب ردا...',
                'of': 'من',
                'results': 'نتيجة',
            };

            Object.assign(builtinArabicTranslations, builtinArabicInstantTranslations);

            function builtinArabicTranslation(text) {
                const normalized = (text || '').replace(/\s+/g, ' ').trim();

                if (! normalized) {
                    return null;
                }

                if (builtinArabicTranslations[normalized]) {
                    return builtinArabicTranslations[normalized];
                }

                const daysAgoMatch = normalized.match(/^(\d+)\s+days?\s+ago$/i);

                if (daysAgoMatch) {
                    return `منذ ${daysAgoMatch[1]} أيام`;
                }

                const hoursAgoMatch = normalized.match(/^(\d+)\s+hours?\s+ago$/i);

                if (hoursAgoMatch) {
                    return `منذ ${hoursAgoMatch[1]} ساعات`;
                }

                const minutesAgoMatch = normalized.match(/^(\d+)\s+minutes?\s+ago$/i);

                if (minutesAgoMatch) {
                    return `منذ ${minutesAgoMatch[1]} دقائق`;
                }

                const dateMatch = normalized.match(/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2}),\s+(\d{4})$/i);

                if (dateMatch) {
                    const months = {
                        Jan: 'يناير',
                        Feb: 'فبراير',
                        Mar: 'مارس',
                        Apr: 'أبريل',
                        May: 'مايو',
                        Jun: 'يونيو',
                        Jul: 'يوليو',
                        Aug: 'أغسطس',
                        Sep: 'سبتمبر',
                        Oct: 'أكتوبر',
                        Nov: 'نوفمبر',
                        Dec: 'ديسمبر',
                    };
                    const monthKey = dateMatch[1].slice(0, 1).toUpperCase() + dateMatch[1].slice(1, 3).toLowerCase();

                    return `${dateMatch[2]} ${months[monthKey] || dateMatch[1]} ${dateMatch[3]}`;
                }

                const dateTimeMatch = normalized.match(/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2}),\s+(\d{4})\s+-\s+(.+)$/i);

                if (dateTimeMatch) {
                    const months = {
                        Jan: 'يناير',
                        Feb: 'فبراير',
                        Mar: 'مارس',
                        Apr: 'أبريل',
                        May: 'مايو',
                        Jun: 'يونيو',
                        Jul: 'يوليو',
                        Aug: 'أغسطس',
                        Sep: 'سبتمبر',
                        Oct: 'أكتوبر',
                        Nov: 'نوفمبر',
                        Dec: 'ديسمبر',
                    };
                    const monthKey = dateTimeMatch[1].slice(0, 1).toUpperCase() + dateTimeMatch[1].slice(1, 3).toLowerCase();

                    return `${dateTimeMatch[2]} ${months[monthKey] || dateTimeMatch[1]} ${dateTimeMatch[3]} - ${dateTimeMatch[4]}`;
                }

                if (normalized.startsWith('From: ')) {
                    return `من: ${normalized.slice(6)}`;
                }

                if (normalized.startsWith('To: ')) {
                    return `إلى: ${normalized.slice(4)}`;
                }

                return null;
            }

            function isTranslatableText(text) {
                if (! text) {
                    return false;
                }

                const normalized = text.replace(/\s+/g, ' ').trim();

                if (! normalized || normalized.length > 180) {
                    return false;
                }

                if (/^[\d\s#.,:;|/\\()[\]{}+\-_%@]+$/.test(normalized)) {
                    return false;
                }

                return /[A-Za-z]/.test(normalized);
            }

            function rememberOriginal(element, key, value) {
                if (! element.dataset[key]) {
                    element.dataset[key] = value;
                }

                return element.dataset[key];
            }

            function collectTranslationTargets() {
                const targets = [];
                const seen = new Set();

                function pushTarget(element, type, original, attribute = null) {
                    const key = `${type}:${attribute || 'text'}:${original}:${targets.length}`;

                    if (seen.has(key)) {
                        return;
                    }

                    seen.add(key);
                    targets.push({
                        element,
                        type,
                        attribute,
                        original,
                    });
                }

                function pushTextNodeTarget(node, original) {
                    const key = `textNode:${original}:${targets.length}`;

                    if (seen.has(key)) {
                        return;
                    }

                    seen.add(key);
                    targets.push({
                        node,
                        type: 'textNode',
                        original,
                    });
                }

                function shouldSkipElement(element) {
                    if (element.matches('.person-meta strong, .person strong, .user-person strong, .agent-person strong, .ticket-person strong')) {
                        return true;
                    }

                    return element.closest([
                        '[data-no-auto-translate]',
                        '.user-box',
                        '.notification-mini-content',
                        '.agent-email',
                        '.ticket-number',
                        '.brand-text',
                        '.brand-mark',
                        '.avatar',
                        '.mini-avatar',
                        'script',
                        'style',
                        'code',
                        'pre'
                    ].join(','));
                }

                document.querySelectorAll('[data-auto-translate]').forEach(element => {
                    const text = rememberOriginal(
                        element,
                        'autoTranslateOriginal',
                        (element.textContent || '').replace(/\s+/g, ' ').trim()
                    );

                    if (isTranslatableText(text)) {
                        pushTarget(element, 'text', text);
                    }
                });

                document.querySelectorAll('[data-auto-translate-attribute]').forEach(element => {
                    const attribute = element.dataset.autoTranslateAttribute;
                    const text = rememberOriginal(
                        element,
                        'autoTranslateOriginal',
                        (element.getAttribute(attribute) || '').replace(/\s+/g, ' ').trim()
                    );

                    if (attribute && isTranslatableText(text)) {
                        pushTarget(element, 'attribute', text, attribute);
                    }
                });

                const uiTextSelectors = [
                    '.main h1',
                    '.main h2',
                    '.main h3',
                    '.main h4',
                    '.main p',
                    '.main small',
                    '.main .page-title',
                    '.main .page-subtitle',
                    '.main .stat-card span',
                    '.main .stat-card small',
                    '.main .stat-card strong',
                    '.main .dashboard-card span',
                    '.main .dashboard-card small',
                    '.main label',
                    '.main th',
                    '.main button',
                    '.main a.btn',
                    '.main .btn',
                    '.main option',
                    '.main .empty-state span',
                    '.main .form-help',
                    '.main .badge',
                    '.main .priority',
                    '.main .ticket-link span',
                    '.main .ticket-title',
                    '.main td[data-label="Department"]',
                    '.main td[data-label="Updated"]',
                    '.main td[data-label="Created"]',
                    '.main .role-badge',
                    '.main .live-search-empty',
                    '.main .notification-summary-card span',
                    '.main .notification-summary-card small',
                ].join(',');

                document.querySelectorAll(uiTextSelectors).forEach(element => {
                    if (
                        shouldSkipElement(element) ||
                        element.matches('.agent-email, .ticket-number, .brand-text, .brand-mark') ||
                        element.closest('.agent-email, .ticket-number, .brand-text, .brand-mark') ||
                        element.children.length > 0
                    ) {
                        return;
                    }

                    const text = rememberOriginal(
                        element,
                        'autoTranslateOriginal',
                        (element.textContent || '').replace(/\s+/g, ' ').trim()
                    );

                    if (! isTranslatableText(text)) {
                        return;
                    }

                    pushTarget(element, 'text', text);
                });

                document.querySelectorAll('.main, .sidebar, .topbar').forEach(scope => {
                    scope.querySelectorAll('*').forEach(element => {
                        if (shouldSkipElement(element) || element.children.length > 0) {
                            return;
                        }

                        const text = rememberOriginal(
                            element,
                            'autoTranslateOriginal',
                            (element.textContent || '').replace(/\s+/g, ' ').trim()
                        );

                        if (! isTranslatableText(text)) {
                            return;
                        }

                        if (/[@]|^RIQ-|^#|^[A-Z]{2,}-\d+/i.test(text)) {
                            return;
                        }

                        pushTarget(element, 'text', text);
                    });

                    const walker = document.createTreeWalker(scope, NodeFilter.SHOW_TEXT, {
                        acceptNode(node) {
                            const parent = node.parentElement;

                            if (! parent || shouldSkipElement(parent) || parent.children.length === 0) {
                                return NodeFilter.FILTER_REJECT;
                            }

                            const text = (node.textContent || '').replace(/\s+/g, ' ').trim();

                            if (! isTranslatableText(text)) {
                                return NodeFilter.FILTER_REJECT;
                            }

                            if (/[@]|^RIQ-|^#|^[A-Z]{2,}-\d+/i.test(text)) {
                                return NodeFilter.FILTER_REJECT;
                            }

                            return NodeFilter.FILTER_ACCEPT;
                        },
                    });

                    let node = walker.nextNode();

                    while (node) {
                        const text = node.__resolveIqOriginalText || (node.textContent || '').replace(/\s+/g, ' ').trim();
                        node.__resolveIqOriginalText = text;
                        pushTextNodeTarget(node, text);
                        node = walker.nextNode();
                    }
                });

                document.querySelectorAll('[placeholder], [title], [aria-label], [data-label]').forEach(element => {
                    ['placeholder', 'title', 'aria-label', 'data-label'].forEach(attribute => {
                        if (! element.hasAttribute(attribute)) {
                            return;
                        }

                        const datasetKey = `autoTranslate${attribute.replace(/(^|-)([a-z])/g, (_, __, char) => char.toUpperCase())}Original`;
                        const text = rememberOriginal(
                            element,
                            datasetKey,
                            (element.getAttribute(attribute) || '').replace(/\s+/g, ' ').trim()
                        );

                        if (! isTranslatableText(text)) {
                            return;
                        }

                        pushTarget(element, 'attribute', text, attribute);
                    });
                });

                return targets;
            }

            let translateAgainTimer = null;
            let isApplyingTranslations = false;

            async function translateInterface() {
                if (currentLocale === 'en' || ! csrfToken || ! translationEndpoint) {
                    return;
                }

                const targets = collectTranslationTargets();
                const uniqueTexts = [...new Set(targets.map(target => target.original))];
                const storageKey = `resolveiq-ui-translations:v6:${currentLocale}`;
                const cachedTranslations = JSON.parse(localStorage.getItem(storageKey) || '{}');
                const missingTexts = uniqueTexts.filter(text => {
                    if (cachedTranslations[text]) {
                        return false;
                    }

                    if (currentLocale === 'ar' && builtinArabicTranslation(text)) {
                        return false;
                    }

                    return true;
                });

                if (! uniqueTexts.length) {
                    return;
                }

                const applyTranslations = (translations) => {
                    isApplyingTranslations = true;

                    targets.forEach(target => {
                        const translated = currentLocale === 'ar'
                            ? (builtinArabicTranslation(target.original) || translations[target.original])
                            : translations[target.original];

                        if (! translated || translated === target.original) {
                            return;
                        }

                        if (target.type === 'attribute') {
                            target.element.setAttribute(target.attribute, translated);
                        } else if (target.type === 'textNode') {
                            target.node.textContent = target.node.textContent.replace(target.original, translated);
                        } else {
                            target.element.textContent = translated;
                        }
                    });

                    document.documentElement.classList.add('ui-translated');
                    window.setTimeout(() => {
                        isApplyingTranslations = false;
                    }, 0);
                };

                try {
                    let translations = {
                        ...(currentLocale === 'ar' ? builtinArabicTranslations : {}),
                        ...cachedTranslations,
                    };

                    applyTranslations(translations);

                    if (missingTexts.length) {
                        const response = await fetch(translationEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                locale: currentLocale,
                                texts: missingTexts,
                            }),
                        });

                        if (! response.ok) {
                            return;
                        }

                        const data = await response.json();
                        translations = {
                            ...translations,
                            ...(data.translations || {}),
                        };

                        localStorage.setItem(storageKey, JSON.stringify(translations));
                        applyTranslations(translations);
                    }
                } catch (error) {
                    console.warn('ResolveIQ translation skipped.', error);
                }
            }

            translateInterface();

            const scheduleTranslateInterface = () => {
                if (currentLocale === 'en' || isApplyingTranslations) {
                    return;
                }

                window.clearTimeout(translateAgainTimer);
                translateAgainTimer = window.setTimeout(() => {
                    translateInterface();
                }, 180);
            };

            const translationObserver = new MutationObserver((mutations) => {
                if (mutations.some(mutation => mutation.addedNodes.length || mutation.type === 'characterData')) {
                    scheduleTranslateInterface();
                }
            });

            if (currentLocale !== 'en') {
                translationObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                });
            }

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

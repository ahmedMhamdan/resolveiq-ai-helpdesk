<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} | ResolveIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <header class="auth-app-navbar">
        <a href="{{ route('home') }}" class="brand auth-app-brand">
            <span class="brand-mark">R</span>
            <span class="brand-text">Resolve<span>IQ</span></span>
        </a>

        <div class="auth-app-actions">
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

            <a href="{{ route('login') }}" class="btn btn-secondary auth-nav-action-btn">Login</a>
        </div>
    </header>

    <main class="auth-page auth-with-navbar">
        <section class="auth-card verification-result-card">
            <div class="verification-result-icon {{ $status === 'success' ? 'is-success' : 'is-error' }}">
                @if ($status === 'success')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                    </svg>
                @endif
            </div>

            <div class="auth-head verification-result-head">
                <h1>{{ $title }}</h1>
                <p>{{ $message }}</p>
            </div>

            <a href="{{ $redirectUrl }}" class="btn btn-primary auth-submit verification-result-btn">
                {{ $buttonText }}
            </a>

            <p class="verification-result-note">
                Redirecting automatically...
            </p>
        </section>
    </main>

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

            window.setTimeout(() => {
                window.location.href = @json($redirectUrl);
            }, 3500);
        })();
    </script>
</body>
</html>

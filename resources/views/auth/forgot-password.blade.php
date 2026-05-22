<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | ResolveIQ</title>
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
        <section class="auth-card auth-password-card">
            <div class="auth-head">
                <h1>Reset your password</h1>
                <p>Enter your email address and we will send you a secure password reset link.</p>
            </div>

            @if (session('status'))
                <div class="flash-message">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger auth-alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                @csrf

                <div class="form-group full">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                        autofocus
                    >
                    <p class="auth-field-hint">
                        Use the same email you registered with in ResolveIQ.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    Send reset link
                </button>

                <div class="auth-bottom-link">
                    Remember your password?
                    <a href="{{ route('login') }}">Back to login</a>
                </div>
            </form>
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

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                const input = document.getElementById(button.dataset.passwordToggle);

                button.addEventListener('click', () => {
                    if (! input) return;

                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    button.classList.toggle('is-visible', isHidden);
                    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
            });
        })();
    </script>
</body>
</html>

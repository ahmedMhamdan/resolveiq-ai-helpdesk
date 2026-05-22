<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Register | ResolveIQ</title>
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
        <section class="auth-card">
            <div class="auth-head">
                <h1>Create account</h1>
                <p>Join ResolveIQ and start creating support tickets easily.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger auth-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="auth-form">
                @csrf

                <div class="form-group full">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter your name"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group full">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                    >
                </div>

                <div class="form-group full">
                    <label for="password">Password</label>
                    <div class="password-input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="password-eye-toggle" data-password-toggle="password" aria-label="Show password">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 4.24A10.6 10.6 0 0 1 12 4c6.5 0 10 8 10 8a16.1 16.1 0 0 1-3.19 4.28"></path>
                                <path d="M6.61 6.61A15.8 15.8 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 5.39-1.39"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group full">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-input-wrap">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm your password"
                            required
                        >
                        <button type="button" class="password-eye-toggle" data-password-toggle="password_confirmation" aria-label="Show password">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"></path>
                                <path d="M9.88 4.24A10.6 10.6 0 0 1 12 4c6.5 0 10 8 10 8a16.1 16.1 0 0 1-3.19 4.28"></path>
                                <path d="M6.61 6.61A15.8 15.8 0 0 0 2 12s3.5 8 10 8a10.8 10.8 0 0 0 5.39-1.39"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    Register
                </button>

                <div class="auth-bottom-link">
                    Already have an account?
                    <a href="{{ route('login') }}">Login</a>
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

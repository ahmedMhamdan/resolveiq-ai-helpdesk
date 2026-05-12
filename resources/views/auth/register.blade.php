<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Register | ResolveIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <a href="{{ route('home') }}" class="brand auth-brand">
                <span class="brand-mark">R</span>
                <span class="brand-text">Resolve<span>IQ</span></span>
            </a>

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
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-group full">
                    <label for="password_confirmation">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm your password"
                        required
                    >
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
</body>
</html>

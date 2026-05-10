<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Login | ResolveIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <a href="{{ url('/') }}" class="brand auth-brand">
                <span class="brand-mark">R</span>
                <span class="brand-text">Resolve<span>IQ</span></span>
            </a>

            <div class="auth-head">
                <h1>Welcome back</h1>
                <p>Login to manage tickets, agents, departments, and AI support tools.</p>
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

            <form action="{{ url('/login') }}" method="POST" class="auth-form">
                @csrf

                <div class="form-group full">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@resolveiq.test"
                        required
                        autofocus
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

                <div class="auth-row">
                    <label class="check-row auth-remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary auth-submit">
                    Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>

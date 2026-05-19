<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Verify Email | ResolveIQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <a href="{{ route('dashboard') }}" class="brand auth-brand">
                <span class="brand-mark">R</span>
                <span class="brand-text">Resolve<span>IQ</span></span>
            </a>

            <div class="auth-head">
                <h1>Verify your email</h1>
                <p>
                    We sent a verification link to your email address.
                    Please verify your email before using tickets.
                </p>
            </div>

            @if (session('success'))
            <div class="verify-success-text">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status') === 'verification-link-sent')
            <div class="verify-success-text">
                A new verification link has been sent to your email address.
            </div>
        @endif

            <div class="auth-form">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <button type="submit" class="btn btn-primary auth-submit">
                        Resend Verification Email
                    </button>
                </form>

                <div class="auth-bottom-link">
                    Already verified?
                    <a href="{{ route('dashboard') }}">Back to Dashboard</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

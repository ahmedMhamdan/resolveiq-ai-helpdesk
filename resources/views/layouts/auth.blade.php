<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | ResolveIQ</title>
    @include('partials.head-assets')
</head>
<body class="auth-body">
    <header class="auth-app-navbar">
        <x-brand class="auth-app-brand" />
        <div class="auth-app-actions">
            <x-theme-toggle id="themeToggle" />
            @yield('auth-action')
        </div>
    </header>
    @yield('content')
</body>
</html>

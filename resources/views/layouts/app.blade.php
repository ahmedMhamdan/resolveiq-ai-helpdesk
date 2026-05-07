<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResolveIQ | @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/resolveiq.css') }}">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Resolve<span>IQ</span></div>

            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">Tickets</a>
                <div class="disabled">Departments</div>
                <div class="disabled">Agents</div>
                <div class="disabled">AI Assistant</div>
                <div class="disabled">Settings</div>
            </nav>
        </aside>

        <main class="main">
            <div class="topbar">
                <input class="search" type="text" placeholder="Search tickets, users, or departments...">

                <div class="user-box">
                    <span>Ahmed M.</span>
                    <span>Admin</span>
                </div>
            </div>

            @yield('content')
        </main>
    </div>
</body>
</html>

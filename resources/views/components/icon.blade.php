@props(['name'])

<svg {{ $attributes }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('dashboard')
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.4"/>
            @break
        @case('tickets')
            <path d="M4 6h16v3a3 3 0 0 0 0 6v3H4v-3a3 3 0 0 0 0-6V6Zm10 0v2m0 3v2m0 3v2"/>
            @break
        @case('departments')
            <path d="M4 20h16M6 20V7l8-3v16m0-10 4 2v8M9 9h2m-2 4h2m-2 4h2"/>
            @break
        @case('agents')
            <path d="M4 13v-1a8 8 0 0 1 16 0v1m0 4v1a2 2 0 0 1-2 2h-4"/>
            <rect x="3" y="12" width="4" height="6" rx="1.5"/>
            <rect x="17" y="12" width="4" height="6" rx="1.5"/>
            @break
        @case('users')
            <circle cx="9" cy="8" r="3"/>
            <path d="M3.5 19v-1a4 4 0 0 1 4-4h3a4 4 0 0 1 4 4v1m2-14a3 3 0 0 1 0 6m.5 3a4 4 0 0 1 3.5 4v1"/>
            @break
        @case('spark')
            <path d="M11 3c1 4.5 2.5 6 7 7-4.5 1-6 2.5-7 7-1-4.5-2.5-6-7-7 4.5-1 6-2.5 7-7Zm7 10v6m-3-3h6"/>
            @break
        @case('book')
            <path d="M12 6v14m0-14C9 4 6 4 3.5 5.5v13C6 17 9 17 12 20c3-3 6-3 8.5-1.5v-13C18 4 15 4 12 6Z"/>
            @break
        @case('profile')
            <circle cx="12" cy="8" r="3.5"/>
            <path d="M5 20v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1"/>
            @break
        @case('inbox')
            <path d="M4 13l2.5-7h11l2.5 7v5H4v-5Zm0 0h5l1.2 2h3.6l1.2-2h5"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7.5v5l3 2"/>
            @break
        @case('check')
            <circle cx="12" cy="12" r="8.5"/>
            <path d="m8.5 12 2.3 2.3 4.7-4.7"/>
            @break
        @case('flag')
            <path d="M5 21V4m0 1c4-3 8 3 14 0v10c-6 3-10-3-14 0"/>
            @break
        @case('arrow')
            <path d="M4.5 12h14m-5-5 5 5-5 5"/>
            @break
        @case('moon')
            <path d="M20 15a8.5 8.5 0 0 1-11-11 8.5 8.5 0 1 0 11 11Z"/>
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('logout')
            <path d="M11 4H5v16h6m2-12 4 4-4 4m-4-4h11"/>
            @break
    @endswitch
</svg>

@extends('layouts.app')

@section('title', __('users.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1 data-auto-translate>{{ __('users.title') }}</h1>
            <p class="page-subtitle" data-auto-translate>
                {{ __('users.subtitle') }}
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin: 0 0 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="margin: 0 0 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-card">
        <div class="table-head users-table-head">
            <div>
                <h2 data-auto-translate>{{ __('users.accounts') }}</h2>
                <p class="page-subtitle" data-auto-translate>
                    {{ __('users.accounts_subtitle') }}
                </p>
            </div>

            <form method="GET" action="{{ route('users.index') }}" class="filters users-search-form">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('users.search_placeholder') }}"
                    data-auto-translate-attribute="placeholder"
                >

                <button type="submit" data-auto-translate>{{ __('users.search') }}</button>

                @if (!empty($search))
                    <a href="{{ route('users.index') }}" class="btn btn-secondary users-search-reset" data-auto-translate>
                        {{ __('users.reset') }}
                    </a>
                @endif

                @if (!empty($search))
                    @php
                        $usersCount = method_exists($users, 'total') ? $users->total() : $users->count();
                    @endphp

                    <div class="users-search-status {{ $usersCount > 0 ? 'is-success' : 'is-warning' }}">
                        @if ($usersCount > 0)
                            {{ __('users.search_applied') }} {{ $usersCount }} {{ __('users.users_found') }} "{{ $search }}".
                        @else
                            {{ __('users.no_users_for') }} "{{ $search }}".
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <div class="table-wrap users-table-wrap">
            <table class="users-management-table users-mobile-table">
                <thead>
                    <tr>
                        <th data-auto-translate>{{ __('users.user') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('users.role') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('users.created_tickets') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('users.change_role') }}</th>
                        <th class="users-center-col" data-auto-translate>{{ __('users.actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        @php
                            $roleName = strtolower($user->role?->name ?? 'user');
                            $isCurrentUser = auth()->id() === $user->id;

                            $avatarUrl = null;

                            if ($user->avatar_path) {
                                $avatarUrl = method_exists($user, 'avatarUrl')
                                    ? $user->avatarUrl()
                                    : (str_starts_with($user->avatar_path, 'images/')
                                        ? asset($user->avatar_path)
                                        : asset('storage/' . $user->avatar_path));
                            }
                        @endphp

                        <tr>
                            <td data-label="{{ __('users.user') }}">
                                <div class="user-person">
                                    <span class="mini-avatar user-list-avatar">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }} avatar">
                                        @else
                                            <span class="avatar-fallback">?</span>
                                        @endif
                                    </span>

                                    <div class="user-person-meta">
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col" data-label="{{ __('users.role') }}">
                                <span class="role-badge role-{{ $roleName }}">
                                    {{ __(ucfirst($roleName)) }}
                                </span>
                            </td>

                            <td class="users-center-col" data-label="{{ __('users.created_tickets') }}">
                                <span class="ticket-count-badge">
                                    {{ $user->tickets_count ?? 0 }}
                                </span>
                            </td>

                            <td class="users-center-col" data-label="{{ __('users.change_role') }}">
                                @if ($roleName === 'admin')
                                    <span class="role-badge role-admin" data-auto-translate>
                                        {{ __('users.protected_admin') }}
                                    </span>
                                @elseif ($isCurrentUser)
                                    <span class="role-badge role-user" data-auto-translate>
                                        {{ __('users.current_account') }}
                                    </span>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('users.updateRole', $user) }}"
                                        class="users-role-form"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select name="role" class="role-select">
                                            <option value="user" @selected($roleName === 'user') data-auto-translate>
                                                {{ __('users.user_role') }}
                                            </option>
                                            <option value="agent" @selected($roleName === 'agent') data-auto-translate>
                                                {{ __('users.agent_role') }}
                                            </option>
                                        </select>

                                        <button type="submit" class="btn btn-sm" data-auto-translate>
                                            {{ __('users.update') }}
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <td class="users-center-col" data-label="{{ __('users.actions') }}">
                                <div class="users-role-actions">
                                    <a href="{{ url('/users/' . $user->id) }}" class="btn btn-secondary btn-sm" data-auto-translate>
                                        {{ __('users.view') }}
                                    </a>

                                    @if ($roleName !== 'admin')
                                        <a href="{{ url('/users/' . $user->id . '/edit') }}" class="btn btn-edit-soft btn-sm" data-auto-translate>
                                            {{ __('users.edit') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="compact-empty-state">
                                    <strong data-auto-translate>{{ __('users.no_users') }}</strong>
                                    <span data-auto-translate>{{ __('users.agents_hidden') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
@endsection
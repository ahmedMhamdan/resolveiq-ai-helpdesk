@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
    <div class="page-head">
        <div>
            <h1>Users Management</h1>
            <p class="page-subtitle">
                Manage customer accounts, review user activity, and promote users to agents when needed.
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
                <h2>Accounts</h2>
                <p class="page-subtitle">
                    Promote normal users to agents. Agents are managed from the Agents page.
                </p>
            </div>

            <form method="GET" action="{{ route('users.index') }}" class="filters">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search name or email..."
                >

                <button type="submit">Search</button>

                @if (!empty($search))
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-wrap users-table-wrap">
            <table class="users-management-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th class="users-center-col">Role</th>
                        <th class="users-center-col">Created Tickets</th>
                        <th class="users-center-col">Change Role</th>
                        <th class="users-center-col">Actions</th>
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
                            <td>
                                <div class="user-person">
                                    <span class="mini-avatar user-list-avatar">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }} avatar">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        @endif
                                    </span>

                                    <div class="user-person-meta">
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                <span class="role-badge role-{{ $roleName }}">
                                    {{ ucfirst($roleName) }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                <span class="ticket-count-badge">
                                    {{ $user->tickets_count }}
                                </span>
                            </td>


                            <td class="users-center-col">
                                @if ($roleName === 'admin')
                                    <span class="role-badge role-admin">
                                        Protected Admin
                                    </span>
                                @elseif ($isCurrentUser)
                                    <span class="role-badge role-user">
                                        Current Account
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
                                            <option value="user" @selected($roleName === 'user')>
                                                User
                                            </option>

                                            <option value="agent" @selected($roleName === 'agent')>
                                                Agent
                                            </option>
                                        </select>

                                        <button type="submit" class="btn btn-sm">
                                            Update
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <td class="users-center-col">
                                <div class="users-role-actions">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                                        View
                                    </a>

                                    @if ($roleName !== 'admin')
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-edit-soft btn-sm">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="compact-empty-state">
                                    <strong>No users found.</strong>
                                    <span>Agents are hidden from this page and managed from the Agents page.</span>
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

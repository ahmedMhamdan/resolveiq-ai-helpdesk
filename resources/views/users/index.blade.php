@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
    <div class="page-head">
        <div>
            <h1>Users Management</h1>
            <p class="page-subtitle">
                Promote normal users to agents or return agents back to users.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger" style="margin: 0 0 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-card">
        <div class="table-head">
            <h2>Accounts</h2>

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

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th class="users-center-col">Role</th>
                        <th class="users-center-col">Created Tickets</th>
                        <th class="users-center-col">Assigned Tickets</th>
                        <th class="users-center-col">Change Role</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        @php
                            $roleName = strtolower($user->role?->name ?? 'user');
                            $isCurrentUser = auth()->id() === $user->id;
                        @endphp

                        <tr>
                            <td>
                                <div class="person">
                                    <span class="mini-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>

                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <br>
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
                                <span class="ticket-count-badge">
                                    {{ $user->assigned_tickets_count }}
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
                                        class="row-actions users-role-actions"
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
                                            Save
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
@endsection

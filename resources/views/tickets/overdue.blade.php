@extends('layouts.app')

@section('title', 'Overdue Tickets')

@section('content')
    <div class="page-head">
        <div>
            <h1>Overdue Tickets</h1>
            <p class="page-subtitle">
                Review tickets that passed their due date and still need action.
            </p>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
        Back to Tickets
    </a>
    </div>

    <div class="table-card overdue-table-card">
        <div class="table-head">
            <h2>Needs Attention</h2>

            <form method="GET" action="{{ route('tickets.overdue') }}" class="filters">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search overdue ticket..."
                >

                <button type="submit">Search</button>

                @if (request('search'))
                    <a href="{{ route('tickets.overdue') }}" class="btn btn-secondary">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="overdue-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th class="users-center-col">Requester</th>
                        <th class="users-center-col">Agent</th>
                        <th class="users-center-col">Department</th>
                        <th class="users-center-col">Due Date</th>
                        <th class="users-center-col">Status</th>
                        <th class="users-center-col">Priority</th>
                        <th class="users-center-col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ticket-link">
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                    <span>{{ $ticket->title }}</span>
                                </a>
                            </td>

                            <td>
                                @php
                                    $requester = $ticket->user;
                                    $requesterAvatarUrl = null;

                                    if ($requester?->avatar_path) {
                                        $requesterAvatarUrl = method_exists($requester, 'avatarUrl')
                                            ? $requester->avatarUrl()
                                            : (str_starts_with($requester->avatar_path, 'images/')
                                                ? asset($requester->avatar_path)
                                                : asset('storage/' . $requester->avatar_path));
                                    }
                                @endphp

                                <div class="person">
                                    <span class="mini-avatar">
                                        @if ($requesterAvatarUrl)
                                            <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? 'Requester' }} avatar">
                                        @else
                                            {{ strtoupper(substr($requester?->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $requester?->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small>{{ $requester?->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                @if ($ticket->agent)
                                    @php
                                        $agent = $ticket->agent;
                                        $agentAvatarUrl = null;

                                        if ($agent?->avatar_path) {
                                            $agentAvatarUrl = method_exists($agent, 'avatarUrl')
                                                ? $agent->avatarUrl()
                                                : (str_starts_with($agent->avatar_path, 'images/')
                                                    ? asset($agent->avatar_path)
                                                    : asset('storage/' . $agent->avatar_path));
                                        }
                                    @endphp

                                    <div class="person overdue-agent-person">
                                        <span class="mini-avatar">
                                            @if ($agentAvatarUrl)
                                                <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                            @else
                                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                                            @endif
                                        </span>

                                        <div>
                                            <strong>{{ $agent->name }}</strong>
                                            <br>
                                            <small>{{ $agent->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="role-badge role-user">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? 'No department' }}
                            </td>

                            <td class="users-center-col">
                                <span class="overdue-date-badge">
                                    {{ \Carbon\Carbon::parse($ticket->due_at)->format('M d, Y') }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                <span class="badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                @if ($ticket->priority)
                                    <span class="priority {{ $ticket->priority }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                @else
                                    <span class="priority unset">
                                        Not set
                                    </span>
                                @endif
                            </td>

                            <td class="users-center-col">
                                <div class="row-actions users-role-actions">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-secondary">
                                        View
                                    </a>

                                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm">
                                        Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">
                                No overdue tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>
@endsection

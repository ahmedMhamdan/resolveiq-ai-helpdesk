@extends('layouts.app')

@section('title', 'Unassigned Tickets')

@section('content')
    <div class="page-head">
        <div>
            <h1>Unassigned Tickets</h1>
            <p class="page-subtitle">
                Review new tickets and assign them to support agents.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin: 0 0 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-card unassigned-table-card">
        <div class="table-head">
            <h2>Waiting for Assignment</h2>

            <form method="GET" action="{{ route('tickets.unassigned') }}" class="filters">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search ticket..."
                >

                <button type="submit">Search</button>

                @if (request('search'))
                    <a href="{{ route('tickets.unassigned') }}" class="btn btn-secondary">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="unassigned-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Requester</th>
                        <th class="users-center-col">Department</th>
                        <th class="users-center-col">Status</th>
                        <th class="users-center-col">Priority</th>
                        <th class="users-center-col">Due Date</th>
                        <th class="users-center-col">Assign Agent</th>
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
                                <div class="person">
                                    <span class="mini-avatar">
                                        @if ($ticket->user?->avatar_path)
                                            <img
                                                src="{{ method_exists($ticket->user, 'avatarUrl') ? $ticket->user->avatarUrl() : (str_starts_with($ticket->user->avatar_path, 'images/') ? asset($ticket->user->avatar_path) : asset('storage/' . $ticket->user->avatar_path)) }}"
                                                alt="{{ $ticket->user->name }} avatar"
                                            >
                                        @else
                                            {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </span>

                                    <div>
                                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                                        <br>
                                        <small>{{ $ticket->user?->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>

                            <td class="users-center-col">
                                {{ $ticket->department?->name ?? 'No department' }}
                            </td>

                            <td class="users-center-col">
                                <span class="badge {{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>

                            <td class="users-center-col">
                                <select
                                    name="priority"
                                    form="assign-ticket-{{ $ticket->id }}"
                                    class="role-select priority-assign-select"
                                >
                                    <option value="" @selected($ticket->priority === null)>
                                        Not set
                                    </option>
                                    <option value="low" @selected($ticket->priority === 'low')>
                                        Low
                                    </option>
                                    <option value="medium" @selected($ticket->priority === 'medium')>
                                        Medium
                                    </option>
                                    <option value="high" @selected($ticket->priority === 'high')>
                                        High
                                    </option>
                                    <option value="urgent" @selected($ticket->priority === 'urgent')>
                                        Urgent
                                    </option>
                                </select>
                            </td>

                            <td class="users-center-col">
                                <input
                                    type="date"
                                    name="due_at"
                                    form="assign-ticket-{{ $ticket->id }}"
                                    value="{{ $ticket->due_at ? $ticket->due_at->format('Y-m-d') : '' }}"
                                    class="unassigned-due-date-input"
                                >
                            </td>

                            <td class="users-center-col">
                                <form
                                    id="assign-ticket-{{ $ticket->id }}"
                                    method="POST"
                                    action="{{ route('tickets.assignAgent', $ticket) }}"
                                    class="row-actions users-role-actions"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <select name="agent_id" class="role-select" required>
                                        <option value="">Select agent</option>

                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->id }}">
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-assign-agent">
                                        Assign
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                No unassigned tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>
@endsection

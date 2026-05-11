@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    @php
        $currentRole = strtolower($role ?? auth()->user()?->role?->name ?? 'user');
        $isAdmin = $currentRole === 'admin';
        $isAgent = $currentRole === 'agent';
        $isUser = $currentRole === 'user';

        $showRequesterColumn = $isAdmin || $isAgent;
        $showAgentColumn = $isAdmin;
        $showPriorityColumn = $isAdmin || $isAgent;

        $columnsCount = 4;

        if ($showRequesterColumn) {
            $columnsCount++;
        }

        if ($showAgentColumn) {
            $columnsCount++;
        }

        if ($showPriorityColumn) {
            $columnsCount++;
        }
    @endphp

    <div class="page-actions tickets-top-actions">
        @if ($isAdmin)
            <a href="{{ route('tickets.trashed') }}" class="btn btn-deleted-tickets">
                Deleted Tickets
            </a>
        @endif

        @if ($isAdmin || $isUser)
            <a href="{{ route('tickets.create') }}" class="btn btn-primary new-ticket-btn">
                + New Ticket
            </a>
        @endif
    </div>

    <section class="card table-card">
        <div class="table-head">
            <form class="filters" method="GET" action="{{ route('tickets.index') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets...">

                <select name="status">
                    <option value="">All Status</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="solved" @selected(request('status') === 'solved')>Solved</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                </select>

                @if ($showPriorityColumn)
                    <select name="priority">
                        <option value="">All Priority</option>
                        <option value="low" @selected(request('priority') === 'low')>Low</option>
                        <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                        <option value="high" @selected(request('priority') === 'high')>High</option>
                        <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
                    </select>
                @endif

                <button type="submit">Filter</button>

                @if(request()->hasAny(['search', 'status', 'priority']))
                    <a class="btn secondary" href="{{ route('tickets.index') }}">Reset</a>
                @endif
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ticket</th>

                    @if ($showRequesterColumn)
                        <th>Requester</th>
                    @endif

                    @if ($showAgentColumn)
                        <th>Agent</th>
                    @endif

                    <th>Department</th>
                    <th>Status</th>

                    @if ($showPriorityColumn)
                        <th>Priority</th>
                    @endif

                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($showRequesterColumn)
                            <td>
                                <div class="person">
                                    <div class="mini-avatar">
                                        {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong><br>
                                        <small>Requester</small>
                                    </div>
                                </div>
                            </td>
                        @endif

                        @if ($showAgentColumn)
                            <td>
                                @if($ticket->agent)
                                    <div class="person">
                                        <div class="mini-avatar">
                                            {{ strtoupper(substr($ticket->agent?->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $ticket->agent->name }}</strong><br>
                                            <small>Agent</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="page-subtitle">Unassigned</span>
                                @endif
                            </td>
                        @endif

                        <td>{{ $ticket->department?->name ?? 'No department' }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>

                        @if ($showPriorityColumn)
                            <td><span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                        @endif

                        <td>{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnsCount }}">
                            <div class="empty">No tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $tickets->links() }}
        </div>
    </section>
@endsection

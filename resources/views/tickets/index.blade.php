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

        $columnsCount = 5;

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
        @if ($role === 'admin')
        <a href="{{ route('tickets.overdue') }}" class="btn btn-overdue-tickets">
            Overdue Tickets
        </a>
        @endif
        @if ($isAdmin || $isUser)
            <a href="{{ route('tickets.create') }}" class="btn btn-primary new-ticket-btn">
                + New Ticket
            </a>
        @endif
        @if ($role === 'admin')
        <a href="{{ route('tickets.unassigned') }}" class="btn btn-unassigned-tickets">
            Unassigned Tickets
        </a>
    @endif
    </div>

    <section class="card table-card tickets-index-card">
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

                @if(request()->filled('search') || request()->filled('status') || request()->filled('priority'))
                    @php
                        $ticketsCount = method_exists($tickets, 'total') ? $tickets->total() : $tickets->count();
                    @endphp

                    <div class="tickets-filter-status {{ $ticketsCount > 0 ? 'is-success' : 'is-warning' }}">
                        @if($ticketsCount > 0)
                            Search applied. {{ $ticketsCount }} ticket{{ $ticketsCount === 1 ? '' : 's' }} found.
                        @else
                            No tickets found for your current search.
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <table class="tickets-table">
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

                    <th>Due Date</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td data-label="Ticket">
                            <a class="ticket-link" href="{{ route('tickets.show', $ticket) }}">
                                <strong>#{{ $ticket->ticket_number }}</strong>
                                <span>{{ $ticket->title }}</span>
                            </a>
                        </td>

                        @if ($showRequesterColumn)
                            <td data-label="Requester">
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
                                    <div class="mini-avatar">
                                        @if ($requesterAvatarUrl)
                                            <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester->name }} avatar">
                                        @else
                                            {{ strtoupper(substr($requester?->name ?? 'U', 0, 1)) }}
                                        @endif
                                    </div>

                                    <div>
                                        <strong>{{ $requester?->name ?? 'Unknown' }}</strong><br>
                                        <small>Requester</small>
                                    </div>
                                </div>
                            </td>
                        @endif

                        @if ($showAgentColumn)
                            <td data-label="Agent">
                                @if($ticket->agent)
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

                                    <div class="person">
                                        <div class="mini-avatar">
                                            @if ($agentAvatarUrl)
                                                <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                            @else
                                                {{ strtoupper(substr($agent?->name ?? 'A', 0, 1)) }}
                                            @endif
                                        </div>

                                        <div>
                                            <strong>{{ $agent->name }}</strong><br>
                                            <small>Agent</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="page-subtitle">Unassigned</span>
                                @endif
                            </td>
                        @endif

                        <td data-label="Department">{{ $ticket->department?->name ?? 'No department' }}</td>
                        <td data-label="Status"><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>

                        @if ($showPriorityColumn)
                            <td data-label="Priority">
                                <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                    {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                                </span>
                            </td>
                        @endif

                        @php
                            $isOverdue = $ticket->due_at
                                && $ticket->due_at->isPast()
                                && ! in_array($ticket->status, ['solved', 'closed'], true);
                        @endphp

                        <td data-label="Due Date">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle">Not set</span>
                            @endif
                        </td>

                        <td data-label="Updated">{{ $ticket->updated_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnsCount }}" data-label="Empty">
                            <div class="empty">No tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $tickets->links('vendor.pagination.resolveiq') }}
        </div>
    </section>
@endsection

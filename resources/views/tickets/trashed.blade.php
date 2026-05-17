@extends('layouts.app')

@section('title', 'Deleted Tickets')

@section('content')
<div class="page-head">
    <div>
        <h1>Deleted Tickets</h1>
        <p>Restore deleted tickets or permanently remove them.</p>
    </div>

    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
        Back to Tickets
    </a>
</div>

<div class="table-card deleted-tickets-card">
    <div class="card-head">
        <div>
            <h2>Deleted Tickets</h2>
            <p>Tickets moved to archive by soft delete.</p>
        </div>
    </div>

    <div class="table-wrap deleted-tickets-wrap">
        <table class="deleted-tickets-table">
            <thead>
                <tr>
                    <th class="col-ticket">Ticket</th>
                    <th class="col-person">Requester</th>
                    <th class="col-person">Agent</th>
                    <th class="col-department">Department</th>
                    <th class="col-status">Status</th>
                    <th class="col-priority">Priority</th>
                    <th class="col-date">Due Date</th>
                    <th class="col-date">Deleted At</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tickets as $ticket)
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

                        $agent = $ticket->agent;
                        $agentAvatarUrl = null;

                        if ($agent?->avatar_path) {
                            $agentAvatarUrl = method_exists($agent, 'avatarUrl')
                                ? $agent->avatarUrl()
                                : (str_starts_with($agent->avatar_path, 'images/')
                                    ? asset($agent->avatar_path)
                                    : asset('storage/' . $agent->avatar_path));
                        }

                        $isOverdue = $ticket->due_at
                            && $ticket->due_at->isPast()
                            && ! in_array($ticket->status, ['solved', 'closed'], true);
                    @endphp

                    <tr>
                        <td class="ticket-cell">
                            <strong class="ticket-number">#{{ $ticket->ticket_number }}</strong>
                            <div class="ticket-title">{{ $ticket->title }}</div>
                        </td>

                        <td>
                            <div class="person ticket-person">
                                <div class="mini-avatar">
                                    @if ($requesterAvatarUrl)
                                        <img src="{{ $requesterAvatarUrl }}" alt="{{ $requester?->name ?? 'Requester' }} avatar">
                                    @else
                                        {{ strtoupper(substr($requester?->name ?? 'U', 0, 1)) }}
                                    @endif
                                </div>

                                <div class="person-meta">
                                    <strong>{{ $requester?->name ?? 'Unknown' }}</strong>
                                    <small>Requester</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($agent)
                                <div class="person ticket-person">
                                    <div class="mini-avatar">
                                        @if ($agentAvatarUrl)
                                            <img src="{{ $agentAvatarUrl }}" alt="{{ $agent->name }} avatar">
                                        @else
                                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                                        @endif
                                    </div>

                                    <div class="person-meta">
                                        <strong>{{ $agent->name }}</strong>
                                        <small>Agent</small>
                                    </div>
                                </div>
                            @else
                                <span class="page-subtitle">Unassigned</span>
                            @endif
                        </td>

                        <td class="department-cell">
                            {{ $ticket->department?->name ?? 'No department' }}
                        </td>

                        <td>
                            <span class="badge {{ $ticket->status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>

                        <td>
                            <span class="priority {{ $ticket->priority ?? 'unset' }}">
                                {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                            </span>
                        </td>

                        <td class="date-cell">
                            @if ($ticket->due_at)
                                <div class="due-date-info {{ $isOverdue ? 'overdue' : '' }}">
                                    <strong>{{ $ticket->due_at->format('M d, Y') }}</strong>
                                    <small>{{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}</small>
                                </div>
                            @else
                                <span class="page-subtitle">Not set</span>
                            @endif
                        </td>

                        <td class="date-cell">
                            {{ $ticket->deleted_at?->diffForHumans() }}
                        </td>

                        <td>
                            <div class="deleted-ticket-actions">
                                <form action="{{ route('tickets.restore', $ticket->id) }}" method="POST">
                                    @csrf

                                    <button type="submit" class="btn btn-sm btn-restore-soft">
                                        Restore
                                    </button>
                                </form>

                                <form action="{{ route('tickets.forceDelete', $ticket->id) }}" method="POST" onsubmit="return confirm('This will permanently delete the ticket. Continue?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger-soft btn-sm">
                                        Delete Forever
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            No deleted tickets found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $tickets->links('vendor.pagination.resolveiq') }}
    </div>
</div>
@endsection

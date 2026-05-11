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

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <strong class="ticket-number">#{{ $ticket->ticket_number }}</strong>
                            <div class="ticket-title">{{ $ticket->title }}</div>
                        </td>

                        <td>
                            {{ $ticket->user?->name ?? 'Unknown' }}
                        </td>

                        <td>
                            {{ $ticket->department?->name ?? 'No department' }}
                        </td>

                        <td>
                            <span class="status-badge status-{{ $ticket->status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>

                        <td>
                            <span class="priority-badge priority-{{ $ticket->priority }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        <td>
                            {{ $ticket->deleted_at?->diffForHumans() }}
                        </td>

                        <td>
                            <div class="row-actions">
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
                        <td colspan="7">
                            No deleted tickets found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $tickets->links() }}
    </div>
</div>
@endsection

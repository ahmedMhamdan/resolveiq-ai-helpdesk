@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <div class="page-actions tickets-top-actions">
    <a href="{{ route('tickets.deleted') }}" class="btn btn-deleted-tickets">
        Deleted Tickets
    </a>

    <a href="{{ route('tickets.create') }}" class="btn btn-primary new-ticket-btn">
        + New Ticket
    </a>
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

                <select name="priority">
                    <option value="">All Priority</option>
                    <option value="low" @selected(request('priority') === 'low')>Low</option>
                    <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                    <option value="high" @selected(request('priority') === 'high')>High</option>
                    <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
                </select>

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
                    <th>Requester</th>
                    <th>Agent</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
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

                        <td>
                            <div class="person">
                                <div class="mini-avatar">{{ strtoupper(substr($ticket->user->name, 0, 1)) }}</div>
                                <div>
                                    <strong>{{ $ticket->user->name }}</strong><br>
                                    <small>Requester</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($ticket->agent)
                                <div class="person">
                                    <div class="mini-avatar">{{ strtoupper(substr($ticket->agent->name, 0, 1)) }}</div>
                                    <div>
                                        <strong>{{ $ticket->agent->name }}</strong><br>
                                        <small>Agent</small>
                                    </div>
                                </div>
                            @else
                                <span class="page-subtitle">Unassigned</span>
                            @endif
                        </td>

                        <td>{{ $ticket->department->name }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td><span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
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

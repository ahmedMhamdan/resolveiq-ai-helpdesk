@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <h1 class="page-title">Tickets</h1>
    <p class="page-subtitle">Manage support requests, priorities, and assigned agents.</p>

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
                            <a href="{{ route('tickets.show', $ticket) }}">
                                #{{ $ticket->ticket_number }}<br>
                                {{ $ticket->title }}
                            </a>
                        </td>
                        <td>{{ $ticket->user->name }}</td>
                        <td>{{ $ticket->agent?->name ?? 'Unassigned' }}</td>
                        <td>{{ $ticket->department->name }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td><span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $tickets->links() }}
        </div>
    </section>
@endsection

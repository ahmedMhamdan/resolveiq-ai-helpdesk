@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Overview of helpdesk tickets and support activity.</p>

    <section class="grid stats">
        <div class="card">
            <div class="stat-label">Open Tickets</div>
            <div class="stat-number">{{ $stats['open'] }}</div>
        </div>

        <div class="card">
            <div class="stat-label">Pending</div>
            <div class="stat-number">{{ $stats['pending'] }}</div>
        </div>

        <div class="card">
            <div class="stat-label">Solved</div>
            <div class="stat-number">{{ $stats['solved'] }}</div>
        </div>

        <div class="card">
            <div class="stat-label">Urgent</div>
            <div class="stat-number">{{ $stats['urgent'] }}</div>
        </div>
    </section>

    <section class="card table-card">
        <div class="table-head">
            <h2>Latest Tickets</h2>
            <a class="btn" href="{{ route('tickets.index') }}">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestTickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}">
                                #{{ $ticket->ticket_number }}<br>
                                {{ $ticket->title }}
                            </a>
                        </td>
                        <td>{{ $ticket->user->name }}</td>
                        <td>{{ $ticket->department->name }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td><span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

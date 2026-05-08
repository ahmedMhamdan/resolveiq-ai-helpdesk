@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Overview of support performance, ticket volume, and urgent issues.</p>
        </div>

        <a class="btn secondary" href="{{ route('tickets.index') }}">View Tickets</a>
    </div>

    <section class="grid stats">
        <div class="card stat-card">
            <div class="stat-top">
                <span>Open Tickets</span>
                <span class="stat-icon">O</span>
            </div>
            <div class="stat-number">{{ $stats['open'] }}</div>
            <div class="stat-trend">Active requests</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Pending</span>
                <span class="stat-icon">P</span>
            </div>
            <div class="stat-number">{{ $stats['pending'] }}</div>
            <div class="stat-trend">Waiting for updates</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Solved</span>
                <span class="stat-icon">S</span>
            </div>
            <div class="stat-number">{{ $stats['solved'] }}</div>
            <div class="stat-trend">Resolved tickets</div>
        </div>

        <div class="card stat-card">
            <div class="stat-top">
                <span>Urgent</span>
                <span class="stat-icon">U</span>
            </div>
            <div class="stat-number">{{ $stats['urgent'] }}</div>
            <div class="stat-trend">Needs attention</div>
        </div>
    </section>

    <section class="card table-card">
        <div class="table-head">
            <div>
                <h2>Latest Tickets</h2>
                <p class="page-subtitle">Newest support requests in the workspace.</p>
            </div>

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

                        <td>{{ $ticket->department->name }}</td>
                        <td><span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td><span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty">No tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

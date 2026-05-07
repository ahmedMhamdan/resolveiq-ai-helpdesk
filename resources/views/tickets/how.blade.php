@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
    <div class="ticket-layout">
        <section>
            <div class="card">
                <div class="ticket-header">
                    <div>
                        <span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span>
                        <h1 class="page-title">#{{ $ticket->ticket_number }}</h1>
                        <p class="page-subtitle">{{ $ticket->title }}</p>
                    </div>

                    <div>
                        <span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
                    </div>
                </div>

                <p>{{ $ticket->description }}</p>
            </div>

            <div class="thread">
                @foreach($ticket->replies as $reply)
                    <div class="reply">
                        <div class="reply-meta">
                            {{ $reply->user->name }} · {{ $reply->created_at->diffForHumans() }}

                            @if($reply->is_internal_note)
                                · Internal note
                            @endif
                        </div>

                        <div>{{ $reply->message }}</div>
                    </div>
                @endforeach
            </div>

            <div class="card" style="margin-top: 16px;">
                <h3>Reply</h3>
                <p class="page-subtitle">Reply form will be connected in the next step.</p>
            </div>
        </section>

        <aside>
            <div class="card">
                <h3>Ticket Details</h3>

                <p><strong>Requester:</strong><br>{{ $ticket->user->name }}</p>
                <p><strong>Agent:</strong><br>{{ $ticket->agent?->name ?? 'Unassigned' }}</p>
                <p><strong>Department:</strong><br>{{ $ticket->department->name }}</p>
                <p><strong>Created:</strong><br>{{ $ticket->created_at->format('M d, Y h:i A') }}</p>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h3>Activity Log</h3>

                @forelse($ticket->activityLogs as $log)
                    <div class="log-item">
                        <strong>{{ $log->action }}</strong><br>
                        <span class="page-subtitle">
                            {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p>No activity yet.</p>
                @endforelse
            </div>

            <div class="card" style="margin-top: 16px;">
                <h3>AI Assistant</h3>
                <p class="page-subtitle">AI summary and suggested replies will be added later.</p>
            </div>
        </aside>
    </div>
@endsection

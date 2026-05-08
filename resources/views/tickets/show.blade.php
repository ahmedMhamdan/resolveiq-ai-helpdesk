@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
    <div class="page-head">
    <div>
        <h1>{{ $ticket->ticket_number }}</h1>
        <p>{{ $ticket->title }}</p>
    </div>

    <div class="page-actions">
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
        Back
    </a>

    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-edit-soft">
    Edit Ticket
    </a>

    <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Move this ticket to deleted tickets?')">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger-soft">
            Delete
        </button>
    </form>
</div>
</div>

    <div class="ticket-layout">
        <section>
            <div class="card">
                <div class="ticket-hero">
                    <div>
                        <span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span>
                        <h1 class="page-title">#{{ $ticket->ticket_number }}</h1>
                        <p class="page-subtitle">{{ $ticket->title }}</p>
                    </div>

                    <span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
                </div>

                <div class="ticket-meta-grid">
                    <div class="meta-box">
                        <small>Requester</small>
                        <strong>{{ $ticket->user->name }}</strong>
                    </div>

                    <div class="meta-box">
                        <small>Agent</small>
                        <strong>{{ $ticket->agent?->name ?? 'Unassigned' }}</strong>
                    </div>

                    <div class="meta-box">
                        <small>Department</small>
                        <strong>{{ $ticket->department->name }}</strong>
                    </div>

                    <div class="meta-box">
                        <small>Created</small>
                        <strong>{{ $ticket->created_at->format('M d, Y') }}</strong>
                    </div>
                </div>

                <p style="margin-top: 18px; line-height: 1.8;">{{ $ticket->description }}</p>
            </div>

            <div class="thread">
                @forelse($ticket->replies as $reply)
                    <div class="reply {{ $reply->is_internal_note ? 'internal' : '' }}">
                        <div class="reply-meta">
                            <div>
                                <span class="reply-author">{{ $reply->user->name }}</span>
                                @if($reply->is_internal_note)
                                    · Internal note
                                @endif
                            </div>

                            <span>{{ $reply->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="reply-body">{{ $reply->message }}</div>
                    </div>
                @empty
                    <div class="card">
                        <p class="page-subtitle">No replies yet.</p>
                    </div>
                @endforelse
            </div>

            <div class="card reply-box">
                <h3>Reply</h3>
                <p class="page-subtitle">Reply form will be connected in the next step.</p>

                <textarea placeholder="Type your reply..."></textarea>

                <div style="margin-top: 12px; display: flex; gap: 10px;">
                    <button class="btn" type="button">Send Reply</button>
                    <button class="btn secondary" type="button">Internal Note</button>
                </div>
            </div>
        </section>

        <aside>
            <div class="card side-card">
                <h3>Ticket Details</h3>

                <div class="detail-row">
                    <small>Status</small>
                    <span class="badge {{ $ticket->status }}">{{ ucfirst($ticket->status) }}</span>
                </div>

                <div class="detail-row">
                    <small>Priority</small>
                    <span class="priority {{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
                </div>

                <div class="detail-row">
                    <small>Requester</small>
                    <strong>{{ $ticket->user->name }}</strong>
                </div>

                <div class="detail-row">
                    <small>Assigned Agent</small>
                    <strong>{{ $ticket->agent?->name ?? 'Unassigned' }}</strong>
                </div>

                <div class="detail-row">
                    <small>Department</small>
                    <strong>{{ $ticket->department->name }}</strong>
                </div>
            </div>

            <div class="card side-card ai-card">
                <h3>AI Assistant</h3>
                <p>
                    AI ticket summaries and suggested replies will be added after the main CRUD workflow is finished.
                </p>

                <button class="btn secondary" type="button">Generate Summary</button>
            </div>

            <div class="card side-card">
                <h3>Activity Log</h3>

                @forelse($ticket->activityLogs as $log)
                    <div class="log-item">
                        <strong>{{ $log->action }}</strong>
                        <span class="page-subtitle">
                            {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="page-subtitle">No activity yet.</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection

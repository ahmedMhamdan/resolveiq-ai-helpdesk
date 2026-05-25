@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
    @php
        $currentUser = auth()->user();
        $role = strtolower($role ?? $currentUser?->role?->name ?? 'user');

        $isAdmin = $role === 'admin';
        $isAgent = $role === 'agent';
        $isUser = $role === 'user';

        $canManageTicket = $isAdmin || ($isAgent && (int) $ticket->agent_id === (int) $currentUser?->id);
        $canCloseTicket = $isAdmin || ($isAgent && (int) $ticket->agent_id === (int) $currentUser?->id);

        $isOverdue = $ticket->due_at
            && $ticket->due_at->isPast()
            && ! in_array($ticket->status, ['solved', 'closed'], true);

        $visibleReplies = $ticket->replies
            ->filter(fn ($reply) => ! $reply->is_internal_note || $isAdmin || $isAgent)
            ->sortBy('created_at');

        $avatarUrl = function ($person) {
            if (! $person || ! $person->avatar_path) {
                return '';
            }

            if (method_exists($person, 'avatarUrl')) {
                return $person->avatarUrl();
            }

            return str_starts_with($person->avatar_path, 'images/')
                ? asset($person->avatar_path)
                : asset('storage/' . $person->avatar_path);
        };
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $ticket->ticket_number }}</h1>
            <p>{{ $ticket->title }}</p>
        </div>

        <div class="page-actions">
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                Back
            </a>

            @if ($canManageTicket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-edit-soft">
                    Edit Ticket
                </a>
            @endif

            @if ($canCloseTicket && $ticket->status !== 'closed')
                <form
                    action="{{ route('tickets.close', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('Close this ticket?')"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-close-ticket">
                        Close Ticket
                    </button>
                </form>
            @endif

            @if ($canCloseTicket && in_array($ticket->status, ['solved', 'closed'], true))
                <form
                    action="{{ route('tickets.reopen', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('Reopen this ticket?')"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-reopen-ticket">
                        Reopen Ticket
                    </button>
                </form>
            @endif

            @if ($isAdmin)
                <form
                    action="{{ route('tickets.destroy', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('Move this ticket to deleted tickets?')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger-soft">
                        Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="ticket-layout">
        <section>
            <div class="card ticket-main-card">
                <div class="ticket-hero">
                    <div>
                        <span class="badge {{ $ticket->status }}">
                            {{ ucfirst($ticket->status) }}
                        </span>

                        <h1 class="page-title">#{{ $ticket->ticket_number }}</h1>
                        <p class="page-subtitle">{{ $ticket->title }}</p>
                    </div>

                    <span class="priority {{ $ticket->priority ?? 'unset' }}">
                        {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                    </span>
                </div>

                <div class="ticket-meta-grid">
                    @if (! $isUser)
                        <div class="meta-box meta-requester">
                            <small>Requester</small>
                            <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                        </div>
                    @endif

                    <div class="meta-box meta-agent">
                        <small>Agent</small>
                        <strong>{{ $ticket->agent?->name ?? 'Unassigned' }}</strong>
                    </div>

                    <div class="meta-box meta-department">
                        <small>Department</small>
                        <strong>{{ $ticket->department?->name ?? 'No department' }}</strong>
                    </div>

                    <div class="meta-box meta-created">
                        <small>Created</small>
                        <strong>{{ $ticket->created_at?->format('M d, Y') }}</strong>
                    </div>

                    <div class="meta-box meta-due {{ $isOverdue ? 'meta-overdue' : '' }}">
                        <small>Due Date</small>

                        <strong>
                            {{ $ticket->due_at ? $ticket->due_at->format('M d, Y') : 'Not set' }}
                        </strong>

                        @if ($ticket->due_at)
                            <span class="{{ $isOverdue ? 'meta-warning' : 'meta-muted-note' }}">
                                {{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>

                <p class="ticket-description">
                    {{ $ticket->description }}
                </p>
            </div>

            <div class="thread">
                @forelse ($visibleReplies as $reply)
                    @php
                        $replyUser = $reply->user;
                        $replyAvatarUrl = $avatarUrl($replyUser);
                        $replyUserName = $replyUser?->name ?? 'Unknown user';
                    @endphp

                    <div class="reply {{ $reply->is_internal_note ? 'internal' : '' }}">
                        <div class="reply-with-avatar">
                            <div class="reply-avatar" title="{{ $replyUserName }}">
                                @if ($replyAvatarUrl)
                                    <img
                                        src="{{ $replyAvatarUrl }}"
                                        alt="{{ $replyUserName }}"
                                        class="reply-avatar-img"
                                    >
                                @else
                                    <span class="avatar-fallback">?</span>
                                @endif
                            </div>

                            <div class="reply-content">
                                <div class="reply-meta">
                                    <div>
                                        <span class="reply-author">
                                            {{ $replyUserName }}
                                        </span>

                                        @if ($reply->is_internal_note && ($isAdmin || $isAgent))
                                            <span class="internal-label">Internal note</span>
                                        @endif
                                    </div>

                                    <span>{{ $reply->created_at?->diffForHumans() }}</span>
                                </div>

                                <div class="reply-body">
                                    {{ $reply->message }}
                                </div>

                                @if ($reply->attachments->count())
                                    <div class="reply-attachments">
                                        @foreach ($reply->attachments as $attachment)
                                            <div class="attachment-chip">
                                                <a
                                                    href="{{ asset('storage/' . $attachment->file_path) }}"
                                                    target="_blank"
                                                    class="attachment-link"
                                                >
                                                    {{ $attachment->file_name }}
                                                </a>

                                                @if ($isAdmin)
                                                    <form
                                                        action="{{ route('tickets.attachments.destroy', [$ticket, $attachment]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this attachment?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="attachment-delete-btn">
                                                            ×
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($isAdmin)
                                    <form
                                        action="{{ route('tickets.replies.destroy', [$ticket, $reply]) }}"
                                        method="POST"
                                        class="reply-delete-form"
                                        onsubmit="return confirm('Delete this reply?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="reply-delete-btn">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-replies">
                        No replies yet.
                    </div>
                @endforelse
            </div>

            <div class="reply-box">
                <form action="{{ route('tickets.replies.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <label for="message">Add Reply</label>

                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Write a reply..."
                        required
                    >{{ old('message') }}</textarea>

                    <div class="reply-options reply-options-before-attachments">
                        @if ($isAdmin || $isAgent)
                            <label class="check-row">
                                <input type="checkbox" name="is_internal_note" value="1">
                                <span>Internal note</span>
                            </label>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            Send Reply
                        </button>
                    </div>

                    <div class="reply-attachments-field">
                        <label for="attachments">Attachments</label>

                        <label for="attachments" class="upload-box" id="uploadBox">
                            <div class="upload-box-icon">+</div>

                            <div class="upload-box-content">
                                <strong>Add attachments</strong>
                                <span id="uploadHint">PNG, JPG, PDF, DOCX — up to 5MB each</span>
                            </div>
                        </label>

                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            hidden
                        >

                        <div class="selected-files-list" id="selectedFilesList"></div>
                    </div>
                </form>
            </div>
        </section>

        <aside>
            <div class="card side-card">
                <h3>Ticket Details</h3>

                <div class="detail-row">
                    <small>Status</small>
                    <span class="badge {{ $ticket->status }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>

                <div class="detail-row">
                    <small>Priority</small>
                    <span class="priority {{ $ticket->priority ?? 'unset' }}">
                        {{ $ticket->priority ? ucfirst($ticket->priority) : 'Not set' }}
                    </span>
                </div>

                <div class="detail-row">
                    <small>Due Date</small>

                    @if ($ticket->due_at)
                        <strong class="{{ $isOverdue ? 'due-overdue-text' : '' }}">
                            {{ $ticket->due_at->format('M d, Y - h:i A') }}
                        </strong>

                        <span class="page-subtitle">
                            {{ $isOverdue ? 'Overdue' : $ticket->due_at->diffForHumans() }}
                        </span>
                    @else
                        <strong>Not set</strong>
                    @endif
                </div>

                @if (! $isUser)
                    <div class="detail-row">
                        <small>Requester</small>
                        <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                    </div>
                @endif

                <div class="detail-row">
                    <small>Assigned Agent</small>
                    <strong>{{ $ticket->agent?->name ?? 'Unassigned' }}</strong>
                </div>

                <div class="detail-row">
                    <small>Department</small>
                    <strong>{{ $ticket->department?->name ?? 'No department' }}</strong>
                </div>

                <div class="detail-row">
                    <small>First Response</small>
                    <strong>
                        {{ $ticket->first_response_at ? $ticket->first_response_at->format('M d, Y - h:i A') : 'Not yet' }}
                    </strong>
                </div>

                <div class="detail-row">
                    <small>Resolved At</small>
                    <strong>
                        {{ $ticket->resolved_at ? $ticket->resolved_at->format('M d, Y - h:i A') : 'Not resolved' }}
                    </strong>
                </div>

                <div class="detail-row">
                    <small>Closed At</small>
                    <strong>
                        {{ $ticket->closed_at ? $ticket->closed_at->format('M d, Y - h:i A') : 'Not closed' }}
                    </strong>
                </div>
            </div>

            @if ($isAdmin || $isAgent)
                <div class="card side-card ai-card ticket-ai-mini-card">
                    <h3>AI Assistant</h3>
                    <p>
                        Generate AI summary, reply, or priority suggestion for this ticket.
                    </p>

                    <a href="{{ route('ai.index', ['ticket_id' => $ticket->id]) }}" class="btn btn-ai-workspace">
                        Open AI Assistant
                    </a>
                </div>

                <div class="card side-card">
                    <h3>Activity Log</h3>

                    @forelse ($ticket->activityLogs as $log)
                        <div class="log-item">
                            <strong>{{ $log->action }}</strong>
                            <span class="page-subtitle">
                                {{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <p class="page-subtitle">No activity yet.</p>
                    @endforelse
                </div>
            @endif
        </aside>
    </div>

    <script>
        (() => {
            const attachmentsInput = document.getElementById('attachments');
            const selectedFilesList = document.getElementById('selectedFilesList');
            const uploadHint = document.getElementById('uploadHint');

            if (!attachmentsInput || !selectedFilesList || !uploadHint) {
                return;
            }

            let selectedFiles = [];

            function syncInputFiles() {
                const dataTransfer = new DataTransfer();

                selectedFiles.forEach(file => {
                    dataTransfer.items.add(file);
                });

                attachmentsInput.files = dataTransfer.files;
            }

            function renderSelectedFiles() {
                selectedFilesList.innerHTML = '';

                if (!selectedFiles.length) {
                    uploadHint.textContent = 'PNG, JPG, PDF, DOCX — up to 5MB each';
                    return;
                }

                uploadHint.textContent = `${selectedFiles.length} file(s) selected`;

                selectedFiles.forEach((file, index) => {
                    const chip = document.createElement('div');
                    chip.className = 'selected-file-chip';

                    const fileName = document.createElement('span');
                    fileName.className = 'selected-file-name';
                    fileName.textContent = file.name;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'remove-selected-file';
                    removeButton.textContent = '×';
                    removeButton.setAttribute('aria-label', `Remove ${file.name}`);

                    removeButton.addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        syncInputFiles();
                        renderSelectedFiles();
                    });

                    chip.appendChild(fileName);
                    chip.appendChild(removeButton);
                    selectedFilesList.appendChild(chip);
                });
            }

            attachmentsInput.addEventListener('change', () => {
                selectedFiles = Array.from(attachmentsInput.files || []);
                syncInputFiles();
                renderSelectedFiles();
            });
        })();
    </script>
@endsection

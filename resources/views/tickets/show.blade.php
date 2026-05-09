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
            <div class="card ticket-main-card">
                <div class="ticket-hero">
                    <div>
                        <span class="badge {{ $ticket->status }}">
                            {{ ucfirst($ticket->status) }}
                        </span>

                        <h1 class="page-title">#{{ $ticket->ticket_number }}</h1>
                        <p class="page-subtitle">{{ $ticket->title }}</p>
                    </div>

                    <span class="priority {{ $ticket->priority }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>

                <div class="ticket-meta-grid">
                <div class="meta-box meta-requester">
                    <small>Requester</small>
                    <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                </div>

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
                    <strong>{{ $ticket->created_at->format('M d, Y') }}</strong>
                </div>
            </div>

                <p class="ticket-description">
                    {{ $ticket->description }}
                </p>
            </div>

            <div class="thread">
                @forelse ($ticket->replies as $reply)
                    <div class="reply {{ $reply->is_internal_note ? 'internal' : '' }}">
                        <div class="reply-meta">
                            <div>
                                <span class="reply-author">
                                    {{ $reply->user?->name ?? 'Unknown user' }}
                                </span>

                                @if ($reply->is_internal_note)
                                    <span class="internal-label">Internal note</span>
                                @endif
                            </div>

                            <span>{{ $reply->created_at->diffForHumans() }}</span>
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
                                </div>
                            @endforeach
                        </div>
                    @endif

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

                    <div class="reply-options">
                        <label class="check-row">
                            <input type="checkbox" name="is_internal_note" value="1">
                            <span>Internal note</span>
                        </label>

                        <button type="submit" class="btn btn-primary">
                            Send Reply
                        </button>
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
                    <span class="priority {{ $ticket->priority }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>

                <div class="detail-row">
                    <small>Requester</small>
                    <strong>{{ $ticket->user?->name ?? 'Unknown' }}</strong>
                </div>

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

            <div class="card side-card ai-card">
                <h3>AI Assistant</h3>
                <p>
                    AI ticket summaries and suggested replies will be added after the main CRUD workflow is finished.
                </p>

                <button class="btn secondary" type="button">
                    Generate Summary
                </button>
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
        </aside>
    </div>
    <script>
        const attachmentsInput = document.getElementById('attachments');
        const selectedFilesList = document.getElementById('selectedFilesList');
        const uploadHint = document.getElementById('uploadHint');

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
                removeButton.className = 'selected-file-remove';
                removeButton.textContent = '×';

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

        attachmentsInput?.addEventListener('change', function () {
            const newFiles = Array.from(this.files);

            newFiles.forEach(file => {
                const exists = selectedFiles.some(existing => {
                    return existing.name === file.name && existing.size === file.size;
                });

                if (!exists) {
                    selectedFiles.push(file);
                }
            });

            syncInputFiles();
            renderSelectedFiles();
        });
    </script>
@endsection

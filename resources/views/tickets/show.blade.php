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
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary" data-auto-translate>
                {{ __('common.back') }}
            </a>

            @if ($canManageTicket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-edit-soft" data-auto-translate>
                    {{ __('tickets.edit_ticket') }}
                </a>
            @endif

            @if ($canCloseTicket && $ticket->status !== 'closed')
                <form
                    action="{{ route('tickets.close', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('{{ __('tickets.confirm_close') }}')"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-close-ticket" data-auto-translate>
                        {{ __('tickets.close_ticket') }}
                    </button>
                </form>
            @endif

            @if ($canCloseTicket && in_array($ticket->status, ['solved', 'closed'], true))
                <form
                    action="{{ route('tickets.reopen', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('{{ __('tickets.confirm_reopen') }}')"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-reopen-ticket" data-auto-translate>
                        {{ __('tickets.reopen_ticket') }}
                    </button>
                </form>
            @endif

            @if ($isAdmin)
                <form
                    action="{{ route('tickets.destroy', $ticket) }}"
                    method="POST"
                    onsubmit="return confirm('{{ __('tickets.confirm_delete') }}')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger-soft" data-auto-translate>
                        {{ __('tickets.delete_ticket') }}
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
                        {{ $ticket->priority ? ucfirst($ticket->priority) : __('common.not_set') }}
                    </span>
                </div>

                <div class="ticket-meta-grid">
                    @if (! $isUser)
                        <div class="meta-box meta-requester">
                            <small data-auto-translate>{{ __('common.requester') }}</small>
                            <strong>{{ $ticket->user?->name ?? __('common.unknown') }}</strong>
                        </div>
                    @endif

                    <div class="meta-box meta-agent">
                        <small data-auto-translate>{{ __('common.agent') }}</small>
                        <strong>{{ $ticket->agent?->name ?? __('common.unassigned') }}</strong>
                    </div>

                    <div class="meta-box meta-department">
                        <small data-auto-translate>{{ __('common.department') }}</small>
                        <strong>{{ $ticket->department?->name ?? __('common.no_department') }}</strong>
                    </div>

                    <div class="meta-box meta-created">
                        <small data-auto-translate>{{ __('common.created') }}</small>
                        <strong>{{ $ticket->created_at?->format('M d, Y') }}</strong>
                    </div>

                    <div class="meta-box meta-due {{ $isOverdue ? 'meta-overdue' : '' }}">
                        <small data-auto-translate>{{ __('common.due_date') }}</small>

                        <strong>
                            {{ $ticket->due_at ? $ticket->due_at->format('M d, Y') : __('common.not_set') }}
                        </strong>

                        @if ($ticket->due_at)
                            <span class="{{ $isOverdue ? 'meta-warning' : 'meta-muted-note' }}">
                                {{ $isOverdue ? __('common.overdue') : $ticket->due_at->diffForHumans() }}
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
                        $replyUserName = $replyUser?->name ?? __('common.unknown');
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
                                            <span class="internal-label" data-auto-translate>{{ __('ai.internal_note') }}</span>
                                        @endif
                                    </div>

                                    <span>{{ $reply->created_at?->diffForHumans() }}</span>
                                </div>

                                <div class="reply-body">
                                    {!! nl2br(e($reply->message)) !!}
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
                                                        onsubmit="return confirm('{{ __('tickets.confirm_delete_attachment') }}')"
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
                                        onsubmit="return confirm('{{ __('tickets.confirm_delete_reply') }}')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="reply-delete-btn" data-auto-translate>
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-replies" data-auto-translate>
                        {{ __('common.no_replies_yet') }}
                    </div>
                @endforelse
            </div>

            <div class="reply-box">
                <form action="{{ route('tickets.replies.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <label for="message" data-auto-translate>{{ __('tickets.add_reply') }}</label>

                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="{{ __('tickets.write_reply') }}"
                        data-auto-translate-attribute="placeholder"
                        required
                    >{{ old('message') }}</textarea>

                    <div class="reply-options reply-options-before-attachments">
                        @if ($isAdmin || $isAgent)
                            <label class="check-row">
                                <input type="checkbox" name="is_internal_note" value="1">
                                <span data-auto-translate>{{ __('ai.internal_note') }}</span>
                            </label>
                        @endif

                        <button type="submit" class="btn btn-primary" data-auto-translate>
                            {{ __('tickets.send_reply') }}
                        </button>
                    </div>

                    <div class="reply-attachments-field">
                        <label for="attachments" data-auto-translate>{{ __('tickets.attachments') }}</label>

                        <label for="attachments" class="upload-box" id="uploadBox">
                            <div class="upload-box-icon">+</div>

                            <div class="upload-box-content">
                                <strong data-auto-translate>{{ __('tickets.add_attachments') }}</strong>
                                <span id="uploadHint" data-auto-translate>{{ __('tickets.upload_hint') }}</span>
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
                <h3 data-auto-translate>{{ __('tickets.ticket_details') }}</h3>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('common.status') }}</small>
                    <span class="badge {{ $ticket->status }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('common.priority') }}</small>
                    <span class="priority {{ $ticket->priority ?? 'unset' }}">
                        {{ $ticket->priority ? ucfirst($ticket->priority) : __('common.not_set') }}
                    </span>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('common.due_date') }}</small>

                    @if ($ticket->due_at)
                        <strong class="{{ $isOverdue ? 'due-overdue-text' : '' }}">
                            {{ $ticket->due_at->format('M d, Y - h:i A') }}
                        </strong>

                        <span class="page-subtitle">
                            {{ $isOverdue ? __('common.overdue') : $ticket->due_at->diffForHumans() }}
                        </span>
                    @else
                        <strong data-auto-translate>{{ __('common.not_set') }}</strong>
                    @endif
                </div>

                @if (! $isUser)
                    <div class="detail-row">
                        <small data-auto-translate>{{ __('common.requester') }}</small>
                        <strong>{{ $ticket->user?->name ?? __('common.unknown') }}</strong>
                    </div>
                @endif

                <div class="detail-row">
                    <small data-auto-translate>{{ __('tickets.assigned_agent') }}</small>
                    <strong>{{ $ticket->agent?->name ?? __('common.unassigned') }}</strong>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('common.department') }}</small>
                    <strong>{{ $ticket->department?->name ?? __('common.no_department') }}</strong>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('tickets.first_response') }}</small>
                    <strong>
                        {{ $ticket->first_response_at ? $ticket->first_response_at->format('M d, Y - h:i A') : __('tickets.not_yet') }}
                    </strong>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('tickets.resolved_at') }}</small>
                    <strong>
                        {{ $ticket->resolved_at ? $ticket->resolved_at->format('M d, Y - h:i A') : __('tickets.not_resolved') }}
                    </strong>
                </div>

                <div class="detail-row">
                    <small data-auto-translate>{{ __('tickets.closed_at') }}</small>
                    <strong>
                        {{ $ticket->closed_at ? $ticket->closed_at->format('M d, Y - h:i A') : __('tickets.not_closed') }}
                    </strong>
                </div>
            </div>

            @if ($isAdmin || $isAgent)
                <div class="card side-card ai-card ticket-ai-mini-card">
                    <h3 data-auto-translate>{{ __('common.ai_assistant') }}</h3>
                    <p data-auto-translate>
                        {{ __('tickets.ai_generate') }}
                    </p>

                    <a href="{{ route('ai.index', ['ticket_id' => $ticket->id]) }}" class="btn btn-ai-workspace" data-auto-translate>
                        {{ __('tickets.open_ai_assistant') }}
                    </a>
                </div>

                <div class="card side-card">
                    <h3 data-auto-translate>{{ __('tickets.activity_log') }}</h3>

                    @forelse ($ticket->activityLogs as $log)
                        <div class="log-item">
                            <strong>{{ $log->action }}</strong>
                            <span class="page-subtitle">
                                {{ $log->user?->name ?? __('common.system') }} · {{ $log->created_at?->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <p class="page-subtitle" data-auto-translate>{{ __('common.no_activity_yet') }}</p>
                    @endforelse
                </div>
            @endif
        </aside>
    </div>

    <script>
        (() => {
            const uploadHint = document.getElementById('uploadHint');
            const selectedFilesList = document.getElementById('selectedFilesList');
            const attachmentsInput = document.getElementById('attachments');
            const uploadMessages = <?php echo json_encode([
                'hint' => __('tickets.upload_hint'),
                'files_selected' => __('tickets.files_selected'),
                'remove' => __('tickets.remove_file'),
            ]); ?>;

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
                    uploadHint.textContent = uploadMessages.hint;
                    return;
                }

                uploadHint.textContent = `${selectedFiles.length} ${uploadMessages.files_selected}`;

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
                    removeButton.setAttribute('aria-label', `${uploadMessages.remove} ${file.name}`);

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

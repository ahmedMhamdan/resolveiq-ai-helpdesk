@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
    @php
        $ticketAi = session('ticket_ai');
        $selectedTicketId = (string) old('ticket_id', request('ticket_id', $ticketAi['ticket_id'] ?? ''));
        $selectedMode = old('mode', $ticketAi['mode'] ?? 'summary');
        $isAdmin = strtolower(auth()->user()?->role?->name ?? 'user') === 'admin';
    @endphp

    <div class="page-head ai-page-head">
        <div>
            <h1 class="page-title">AI Assistant</h1>
            <p class="page-subtitle">
                Generate ticket summaries, suggested replies, priority recommendations, or a custom AI response.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" style="margin: 0 0 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ai-page-layout">
        <section class="card ai-workspace-card">
            <div class="ai-section-head">
                <div>
                    <h2>Assistant Workspace</h2>
                    <p>Select a ticket, choose an AI action, and add instructions only when needed.</p>
                </div>
            </div>

            <form
                id="aiGenerateForm"
                method="POST"
                action=""
                class="ai-form-area"
            >
                @csrf

                <input type="hidden" name="mode" id="aiModeInput" value="{{ $selectedMode }}">

                <div class="form-group full">
                    <label for="ticket_id">Ticket</label>

                    <select id="ticket_id" name="ticket_id" required>
                        <option value="">Select ticket</option>

                        @foreach ($tickets as $ticket)
                            <option
                                value="{{ $ticket->id }}"
                                @selected($selectedTicketId === (string) $ticket->id)
                                data-generate-url="{{ route('tickets.ai.generate', $ticket) }}"
                            >
                                #{{ $ticket->ticket_number }} — {{ $ticket->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ai-action-grid ai-action-grid-four">
                    <button
                        type="button"
                        class="ai-action-card {{ $selectedMode === 'summary' ? 'active' : '' }}"
                        data-mode="summary"
                    >
                        <span>Summary</span>
                        <strong>Generate Summary</strong>
                        <small>Create a structured internal summary for the support team.</small>
                    </button>

                    <button
                        type="button"
                        class="ai-action-card {{ $selectedMode === 'reply' ? 'active' : '' }}"
                        data-mode="reply"
                    >
                        <span>Reply</span>
                        <strong>Suggest Reply</strong>
                        <small>Draft a clear customer-facing support response.</small>
                    </button>

                    <button
                        type="button"
                        class="ai-action-card {{ $selectedMode === 'priority' ? 'active' : '' }}"
                        data-mode="priority"
                    >
                        <span>Priority</span>
                        <strong>Suggest Priority</strong>
                        <small>Recommend the ticket priority with a short reason.</small>
                    </button>

                    <button
                        type="button"
                        class="ai-action-card {{ $selectedMode === 'custom' ? 'active' : '' }}"
                        data-mode="custom"
                    >
                        <span>Custom</span>
                        <strong>Custom Instruction</strong>
                        <small>Ask anything specific about this ticket using your own instruction.</small>
                    </button>
                </div>

                <div class="form-group full">
                    <label for="custom_prompt">
                        Additional instructions
                        <span style="color: var(--muted);">(optional, required for Custom)</span>
                    </label>

                    <textarea
                        id="custom_prompt"
                        name="custom_prompt"
                        rows="6"
                        placeholder="Optional: make it shorter, use a friendly tone, focus on security steps, or ask a custom question about this ticket..."
                    >{{ old('custom_prompt') }}</textarea>
                </div>

                <div class="ai-generate-row">
                    <button type="submit" class="btn btn-primary" id="generateAiBtn">
                        Generate Response
                    </button>
                </div>
            </form>
        </section>

        <aside class="card ai-output-card">
            <div class="ai-output-icon">AI</div>

            <h2>AI Output</h2>

            @if ($ticketAi)
                <div class="ai-output-box has-output" id="aiOutputBox">
                    <strong>{{ $ticketAi['title'] ?? 'AI Result' }}</strong>
                    <div class="ai-generated-text">
                        {!! nl2br(e($ticketAi['body'] ?? '')) !!}
                    </div>
                </div>

                @if (($ticketAi['mode'] ?? '') !== 'priority')
                    <form
                        action="{{ route('ai.useAsReply') }}"
                        method="POST"
                        id="aiUseReplyForm"
                        class="ai-use-reply-form"
                    >
                        @csrf

                        <input type="hidden" name="ticket_id" value="{{ $ticketAi['ticket_id'] ?? $selectedTicketId }}">
                        <input type="hidden" name="message" value="{{ $ticketAi['body'] ?? '' }}">
                        <input type="hidden" name="is_internal_note" id="aiInternalNoteInput" value="0">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            onclick="document.getElementById('aiInternalNoteInput').value = 0"
                        >
                            Use as Reply
                        </button>

                        <button
                            type="submit"
                            class="btn btn-edit-soft"
                            onclick="document.getElementById('aiInternalNoteInput').value = 1"
                        >
                            Use as Internal Note
                        </button>
                    </form>
                @endif

                @if ($isAdmin && ($ticketAi['mode'] ?? '') === 'priority' && ! empty($ticketAi['suggested_priority']))
                    <form
                        action="{{ route('tickets.ai.applyPriority', $ticketAi['ticket_id'] ?? $selectedTicketId) }}"
                        method="POST"
                        class="ai-use-reply-form"
                    >
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="priority" value="{{ $ticketAi['suggested_priority'] }}">

                        <button type="submit" class="btn btn-primary">
                            Apply {{ ucfirst($ticketAi['suggested_priority']) }} Priority
                        </button>
                    </form>
                @endif
            @else
                <div class="ai-output-box" id="aiOutputBox">
                    <strong>No output yet</strong>
                    <span>Select a ticket and generate an AI response.</span>
                </div>
            @endif
        </aside>
    </div>

    <script>
        (() => {
            const aiCards = document.querySelectorAll('.ai-action-card');
            const promptBox = document.getElementById('custom_prompt');
            const ticketSelect = document.getElementById('ticket_id');
            const generateForm = document.getElementById('aiGenerateForm');
            const generateBtn = document.getElementById('generateAiBtn');
            const modeInput = document.getElementById('aiModeInput');

            function selectedOption() {
                return ticketSelect?.options[ticketSelect.selectedIndex] || null;
            }

            function selectedMode() {
                return modeInput?.value || 'summary';
            }

            function updatePromptPlaceholder() {
                if (!promptBox) {
                    return;
                }

                const placeholders = {
                    summary: 'Optional: make the summary shorter, or focus on technical details...',
                    reply: 'Optional: use a friendly tone, make it shorter, apologize politely, or write in Arabic...',
                    priority: 'Optional: explain why this should be urgent/high/medium/low...',
                    custom: 'Required for Custom: ask anything specific, e.g. "Give me troubleshooting steps" or "Write a WhatsApp-style update"...',
                };

                promptBox.placeholder = placeholders[selectedMode()] || placeholders.summary;
            }

            function syncGenerateForm() {
                const option = selectedOption();
                const hasTicket = option && option.value;

                if (generateForm) {
                    generateForm.action = hasTicket ? option.dataset.generateUrl : '';
                }

                if (generateBtn) {
                    generateBtn.disabled = !hasTicket;
                    generateBtn.style.opacity = hasTicket ? '1' : '.6';
                    generateBtn.style.cursor = hasTicket ? 'pointer' : 'not-allowed';
                }

                updatePromptPlaceholder();
            }

            aiCards.forEach(card => {
                card.addEventListener('click', () => {
                    aiCards.forEach(item => item.classList.remove('active'));
                    card.classList.add('active');

                    if (modeInput) {
                        modeInput.value = card.dataset.mode || 'summary';
                    }

                    updatePromptPlaceholder();
                });
            });

            ticketSelect?.addEventListener('change', syncGenerateForm);

            generateForm?.addEventListener('submit', event => {
                const option = selectedOption();

                if (!option || !option.value) {
                    event.preventDefault();
                    alert('Please select a ticket first.');
                    return;
                }

                if (selectedMode() === 'custom' && !promptBox?.value.trim()) {
                    event.preventDefault();
                    alert('Please write a custom instruction first.');
                }
            });

            syncGenerateForm();
        })();
    </script>
@endsection

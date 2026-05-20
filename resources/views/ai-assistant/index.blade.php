@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')

    @php
        $ticketAi = session('ticket_ai');
        $selectedTicketId = (string) old('ticket_id', request('ticket_id', $ticketAi['ticket_id'] ?? ''));
        $selectedMode = old('mode', $ticketAi['mode'] ?? 'summary');
        $isAdmin = strtolower(auth()->user()?->role?->name ?? 'user') === 'admin';
        $showReplyActions = $ticketAi && in_array(($ticketAi['mode'] ?? ''), ['summary', 'reply', 'custom'], true);
        $showPriorityAction = $isAdmin && $ticketAi && ($ticketAi['mode'] ?? '') === 'priority' && ! empty($ticketAi['suggested_priority']);
        $showDueDateAction = $isAdmin && $ticketAi && ($ticketAi['mode'] ?? '') === 'due_date' && ! empty($ticketAi['suggested_due_date']);
        $priorityActionUrl = ! empty($ticketAi['ticket_id'])
            ? route('tickets.ai.applyPriority', $ticketAi['ticket_id'])
            : '#';
        $dueDateActionUrl = ! empty($ticketAi['ticket_id'])
            ? route('tickets.ai.applyDueDate', $ticketAi['ticket_id'])
            : '#';
        $showAnyActions = $showReplyActions || $showPriorityAction || $showDueDateAction;
    @endphp

    <div class="page-head ai-page-head">
        <div>
            <h1 class="page-title">AI Assistant</h1>
            <p class="page-subtitle">
                Use ticket context to summarize issues, draft replies, recommend priority, suggest due dates, or generate a custom response.
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
                    <p>Select a ticket, choose an action, then generate a focused support response.</p>
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

                <div class="ai-action-grid ai-action-grid-five">
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
                        class="ai-action-card {{ $selectedMode === 'due_date' ? 'active' : '' }}"
                        data-mode="due_date"
                    >
                        <span>Due Date</span>
                        <strong>Suggest Due Date</strong>
                        <small>Recommend an SLA-style due date based on ticket urgency.</small>
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
                        Instructions
                        <span style="color: var(--muted);">(optional, required for Custom)</span>
                    </label>

                    <textarea
                        id="custom_prompt"
                        name="custom_prompt"
                        rows="6"
                        placeholder="Optional: adjust tone, language, length, or ask a specific question about this ticket..."
                    >{{ old('custom_prompt') }}</textarea>
                </div>

                <div class="ai-generate-row">
                    <button type="submit" class="btn btn-primary ai-generate-btn" id="generateAiBtn">
                        <span class="ai-btn-spinner" aria-hidden="true"></span>
                        <span class="ai-btn-text">Generate</span>
                        <span class="ai-btn-dots" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="card ai-output-card">
            <div class="ai-output-icon">AI</div>

            <h2>AI Output</h2>

            <div class="ai-output-box {{ $ticketAi ? 'has-output' : '' }}" id="aiOutputBox">
                <div id="aiOutputContent">
                    @if ($ticketAi)
                        <strong>{{ $ticketAi['title'] ?? 'AI Result' }}</strong>

                        @if (! empty($ticketAi['used_fallback']))
                            <div class="ai-fallback-notice">
                                The AI provider did not return a valid result after multiple attempts, so ResolveIQ used the local fallback output.
                            </div>
                        @endif

                        @php
                            $aiLines = collect(preg_split('/\r\n|\r|\n/', trim($ticketAi['body'] ?? '')))
                                ->filter(fn ($line) => trim($line) !== '');
                        @endphp

                        <div class="ai-generated-text">
                            @foreach ($aiLines as $line)
                                @php
                                    $cleanLine = trim($line);
                                    $isStrongLine = preg_match('/^([0-9]+[\.)]\s|[-*]\s|\*\*|#+\s|Priority:|Reason:|Main issue:|Important context:|Current status:|Recommended next step:|Due Date:|Suggested Due Date:|Created:|Assignment:|Customer\/support conversation:|Status and priority changes:|Current state:|Recommended follow-up:|تاريخ الاستحقاق|الملخص|تحليل|القسم|الحالة|الأولوية|الخطوة|السبب|المشكلة|السياق|التوصية|المتابعة)/iu', $cleanLine);
                                @endphp

                                <p class="ai-output-line {{ $isStrongLine ? 'ai-output-line-strong' : '' }}">
                                    {{ $cleanLine }}
                                </p>
                            @endforeach
                        </div>

                        @if (! empty($ticketAi['knowledge_sources']))
                            <div class="ai-kb-sources">
                                <strong>
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 19.5V5.75C4 4.78 4.78 4 5.75 4H10C11.1 4 12 4.9 12 6V20C12 18.9 11.1 18 10 18H5.5C4.67 18 4 18.67 4 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 19.5V5.75C20 4.78 19.22 4 18.25 4H14C12.9 4 12 4.9 12 6V20C12 18.9 12.9 18 14 18H18.5C19.33 18 20 18.67 20 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Knowledge sources used
                                </strong>

                                <ul>
                                    @foreach ($ticketAi['knowledge_sources'] as $source)
                                        <li>
                                            {{ is_array($source) ? ($source['title'] ?? 'Knowledge article') : $source }}
                                            @if (is_array($source) && ! empty($source['score']))
                                                <small>— relevance {{ $source['score'] }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @else
                    <div class="ai-empty-state">
                        <div class="ai-empty-orb">
                            <svg
                                class="ai-empty-svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 3l1.7 4.8L18.5 9.5l-4.8 1.7L12 16l-1.7-4.8L5.5 9.5l4.8-1.7L12 3z" />
                                <path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15z" />
                                <path d="M5 14l.6 1.7L7.3 16.3l-1.7.6L5 18.6l-.6-1.7-1.7-.6 1.7-.6L5 14z" />
                            </svg>
                        </div>

                        <strong>No AI output yet</strong>

                        <span>
                            Select a ticket, choose an action, then generate.
                        </span>
                    </div>
                    @endif
                </div>

                <div
                    id="aiOutputActions"
                    class="ai-output-actions"
                    style="display: {{ $showAnyActions ? 'block' : 'none' }}; margin-top: 22px;"
                >
                    <form
                        action="{{ route('ai.useAsReply') }}"
                        method="POST"
                        id="aiUseReplyForm"
                        class="ai-use-reply-form"
                        style="display: {{ $showReplyActions ? 'flex' : 'none' }};"
                    >
                        @csrf

                        <input type="hidden" name="ticket_id" id="aiReplyTicketInput" value="{{ $ticketAi['ticket_id'] ?? $selectedTicketId }}">
                        <textarea name="message" id="aiReplyMessageInput" hidden>{{ $ticketAi['body'] ?? '' }}</textarea>
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

                    @if ($isAdmin)
                        <form
                            action="{{ $priorityActionUrl }}"
                            method="POST"
                            id="aiApplyPriorityForm"
                            class="ai-use-reply-form"
                            data-action-template="{{ route('tickets.ai.applyPriority', '__TICKET_ID__') }}"
                            style="display: {{ $showPriorityAction ? 'flex' : 'none' }};"
                        >
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="priority" id="aiSuggestedPriorityInput" value="{{ $ticketAi['suggested_priority'] ?? '' }}">

                            <button type="submit" class="btn btn-primary" data-priority-button>
                                @if (! empty($ticketAi['suggested_priority']))
                                    Apply {{ ucfirst($ticketAi['suggested_priority']) }} Priority
                                @else
                                    Apply Priority
                                @endif
                            </button>
                        </form>

                        <form
                            action="{{ $dueDateActionUrl }}"
                            method="POST"
                            id="aiApplyDueDateForm"
                            class="ai-use-reply-form"
                            data-action-template="{{ route('tickets.ai.applyDueDate', '__TICKET_ID__') }}"
                            style="display: {{ $showDueDateAction ? 'flex' : 'none' }};"
                        >
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="due_at" id="aiSuggestedDueDateInput" value="{{ $ticketAi['suggested_due_date'] ?? '' }}">

                            <button type="submit" class="btn btn-primary" data-due-date-button>
                                @if (! empty($ticketAi['suggested_due_date']))
                                    Apply Due Date {{ $ticketAi['suggested_due_date'] }}
                                @else
                                    Apply Due Date
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
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
            const outputBox = document.getElementById('aiOutputBox');
            const outputContent = document.getElementById('aiOutputContent');
            const outputActions = document.getElementById('aiOutputActions');
            const useReplyForm = document.getElementById('aiUseReplyForm');
            const replyTicketInput = document.getElementById('aiReplyTicketInput');
            const replyMessageInput = document.getElementById('aiReplyMessageInput');
            const applyPriorityForm = document.getElementById('aiApplyPriorityForm');
            const suggestedPriorityInput = document.getElementById('aiSuggestedPriorityInput');
            const priorityButton = applyPriorityForm?.querySelector('[data-priority-button]');
            const applyDueDateForm = document.getElementById('aiApplyDueDateForm');
            const suggestedDueDateInput = document.getElementById('aiSuggestedDueDateInput');
            const dueDateButton = applyDueDateForm?.querySelector('[data-due-date-button]');
            const canApplyPriority = @json($isAdmin);
            const canApplyDueDate = @json($isAdmin);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value || '';

            function selectedOption() {
                return ticketSelect?.options[ticketSelect.selectedIndex] || null;
            }

            function selectedMode() {
                return modeInput?.value || 'summary';
            }

            function escapeHtml(value) {
                const element = document.createElement('div');
                element.textContent = value ?? '';
                return element.innerHTML;
            }

            function isStrongAiLine(line) {
                return /^(\d+[\.)]\s|[-*]\s|\*\*|#+\s|Priority:|Reason:|Main issue:|Important context:|Current status:|Recommended next step:|Created:|Assignment:|Customer\/support conversation:|Status and priority changes:|Current state:|Recommended follow-up:|Due Date:|Suggested Due Date:|تاريخ الاستحقاق|الملخص|تحليل|القسم|الحالة|الأولوية|الخطوة|السبب|المشكلة|السياق|التوصية|المتابعة)/iu.test(line);
            }

            function renderAiText(text) {
                const lines = String(text || '')
                    .split(/\r\n|\r|\n/)
                    .map(line => line.trim())
                    .filter(Boolean);

                if (!lines.length) {
                    return '<p class="ai-output-line">No content returned.</p>';
                }

                return lines.map(line => {
                    const strongClass = isStrongAiLine(line) ? ' ai-output-line-strong' : '';

                    return `<p class="ai-output-line${strongClass}">${escapeHtml(line)}</p>`;
                }).join('');
            }


            function renderKnowledgeSources(sources) {
                if (!Array.isArray(sources) || !sources.length) {
                    return '';
                }

                const items = sources.map(source => {
                    const title = typeof source === 'string'
                        ? source
                        : (source?.title || 'Knowledge article');

                    const score = typeof source === 'object' && source?.score
                        ? ` <small>— relevance ${escapeHtml(source.score)}</small>`
                        : '';

                    return `<li>${escapeHtml(title)}${score}</li>`;
                }).join('');

                return `
                    <div class="ai-kb-sources">
                        <strong>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 19.5V5.75C4 4.78 4.78 4 5.75 4H10C11.1 4 12 4.9 12 6V20C12 18.9 11.1 18 10 18H5.5C4.67 18 4 18.67 4 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 19.5V5.75C20 4.78 19.22 4 18.25 4H14C12.9 4 12 4.9 12 6V20C12 18.9 12.9 18 14 18H18.5C19.33 18 20 18.67 20 19.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Knowledge sources used
                        </strong>
                        <ul>${items}</ul>
                    </div>
                `;
            }

            function renderAiOutput(ticketAi) {
                if (!outputBox || !outputContent || !ticketAi) {
                    return;
                }

                const fallbackNotice = ticketAi.used_fallback
                    ? '<div class="ai-fallback-notice">The AI provider did not return a valid result after multiple attempts, so ResolveIQ used the local fallback output.</div>'
                    : '';

                outputBox.classList.remove('is-loading');
                outputBox.classList.add('has-output');
                outputContent.innerHTML = `
                    <strong>${escapeHtml(ticketAi.title || 'AI Result')}</strong>
                    ${fallbackNotice}
                    <div class="ai-generated-text">
                        ${renderAiText(ticketAi.body || '')}
                    </div>
                    ${renderKnowledgeSources(ticketAi.knowledge_sources || [])}
                `;

                if (replyTicketInput) {
                    replyTicketInput.value = ticketAi.ticket_id || '';
                }

                if (replyMessageInput) {
                    replyMessageInput.value = ticketAi.body || '';
                }

                const showReplyActions = ['summary', 'reply', 'custom'].includes(ticketAi.mode);
                const suggestedPriority = ticketAi.suggested_priority || '';
                const suggestedDueDate = ticketAi.suggested_due_date || '';
                const showPriorityForm = Boolean(canApplyPriority && ticketAi.mode === 'priority' && suggestedPriority);
                const showDueDateForm = Boolean(canApplyDueDate && ticketAi.mode === 'due_date' && suggestedDueDate);
                const showAnyActions = showReplyActions || showPriorityForm || showDueDateForm;

                if (outputActions) {
                    outputActions.style.display = showAnyActions ? 'block' : 'none';
                }

                if (useReplyForm) {
                    useReplyForm.style.display = showReplyActions ? 'flex' : 'none';
                }

                if (applyPriorityForm) {
                    applyPriorityForm.style.display = showPriorityForm ? 'flex' : 'none';

                    if (showPriorityForm) {
                        const template = applyPriorityForm.dataset.actionTemplate || '';
                        applyPriorityForm.action = template.replace('__TICKET_ID__', ticketAi.ticket_id);

                        if (suggestedPriorityInput) {
                            suggestedPriorityInput.value = suggestedPriority;
                        }

                        if (priorityButton) {
                            priorityButton.textContent = `Apply ${suggestedPriority.charAt(0).toUpperCase() + suggestedPriority.slice(1)} Priority`;
                        }
                    }
                }

                if (applyDueDateForm) {
                    applyDueDateForm.style.display = showDueDateForm ? 'flex' : 'none';

                    if (showDueDateForm) {
                        const template = applyDueDateForm.dataset.actionTemplate || '';
                        applyDueDateForm.action = template.replace('__TICKET_ID__', ticketAi.ticket_id);

                        if (suggestedDueDateInput) {
                            suggestedDueDateInput.value = suggestedDueDate;
                        }

                        if (dueDateButton) {
                            dueDateButton.textContent = `Apply Due Date ${suggestedDueDate}`;
                        }
                    }
                }
            }

            function updatePromptPlaceholder() {
                if (!promptBox) {
                    return;
                }

                const placeholders = {
                    summary: 'Optional: make the summary shorter, write it in Arabic, or include a specific field like due date...',
                    due_date: 'Optional: write the due date reason in Arabic, make it urgent, or explain it briefly...',
                    reply: 'Optional: use a friendly tone, make it shorter, apologize politely, or write in Arabic...',
                    priority: 'Optional: explain why this should be urgent/high/medium/low...',
                    custom: 'Required for Custom: ask anything specific, e.g. "Give me troubleshooting steps" or "Write a WhatsApp-style update"...',
                };

                promptBox.placeholder = placeholders[selectedMode()] || placeholders.summary;
            }

            function buildAiRequestFormData() {
                return new FormData(generateForm);
            }

            function setLoading(isLoading) {
                const option = selectedOption();
                const hasTicket = option && option.value;
                const btnText = generateBtn?.querySelector('.ai-btn-text');

                if (!generateBtn) {
                    return;
                }

                generateBtn.disabled = isLoading || !hasTicket;
                generateBtn.classList.toggle('is-loading', isLoading);
                generateBtn.style.opacity = hasTicket ? '1' : '.6';
                generateBtn.style.cursor = isLoading ? 'wait' : (hasTicket ? 'pointer' : 'not-allowed');

                if (btnText) {
                    btnText.textContent = isLoading ? 'Generating' : 'Generate';
                }
            }

            function syncGenerateForm() {
                const option = selectedOption();
                const hasTicket = option && option.value;

                if (generateForm) {
                    generateForm.action = hasTicket ? option.dataset.generateUrl : '';
                }

                setLoading(false);
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

            generateForm?.addEventListener('submit', async event => {
                event.preventDefault();

                const option = selectedOption();

                if (!option || !option.value) {
                    alert('Please select a ticket first.');
                    return;
                }

                if (selectedMode() === 'custom' && !promptBox?.value.trim()) {
                    alert('Please write a custom instruction first.');
                    return;
                }

                setLoading(true);

                if (outputBox) {
                    outputBox.classList.add('is-loading');
                }

                if (outputActions) {
                    outputActions.style.display = 'none';
                }

                try {
                    const response = await fetch(generateForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: buildAiRequestFormData(),
                    });

                    const rawResponse = await response.text();
                    let data = null;

                    try {
                        data = rawResponse ? JSON.parse(rawResponse) : {};
                    } catch (parseError) {
                        console.error('AI endpoint returned non-JSON response:', rawResponse);
                        throw new Error('AI endpoint returned a non-JSON response. Check the Laravel terminal or Network tab for the real backend error.');
                    }

                    if (!response.ok) {
                        const errors = data.errors ? Object.values(data.errors).flat().join('\n') : null;
                        throw new Error(errors || data.message || 'Could not generate AI output.');
                    }

                    renderAiOutput(data.ticket_ai);
                } catch (error) {
                    alert(error.message || 'Could not generate AI output.');

                    if (outputBox) {
                        outputBox.classList.remove('is-loading');
                    }
                } finally {
                    setLoading(false);
                }
            });

            syncGenerateForm();
        })();
    </script>
@endsection

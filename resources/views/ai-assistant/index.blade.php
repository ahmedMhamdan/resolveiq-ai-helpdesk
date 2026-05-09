@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
<div class="page-head ai-page-head">
    <div>
        <h1 class="page-title">AI Assistant</h1>
        <p class="page-subtitle">Generate summaries, reply suggestions, and ticket insights.</p>
    </div>
</div>

<div class="ai-page-layout">
    <section class="card ai-workspace-card">
        <div class="ai-section-head">
            <div>
                <h2>Assistant Workspace</h2>
                <p>Select a ticket and choose what you want the assistant to generate.</p>
            </div>
        </div>

        <div class="ai-form-area">
            <div class="form-group full">
                <label for="ticket_id">Ticket</label>

                <select id="ticket_id" name="ticket_id">
                    <option value="">Select ticket</option>

                    @foreach ($tickets as $ticket)
                        <option
                            value="{{ $ticket->id }}"
                            data-number="{{ $ticket->ticket_number }}"
                            data-title="{{ $ticket->title }}"
                            data-status="{{ $ticket->status }}"
                            data-priority="{{ $ticket->priority }}"
                            data-department="{{ $ticket->department?->name ?? 'No department' }}"
                            data-requester="{{ $ticket->user?->name ?? 'Unknown requester' }}"
                            data-agent="{{ $ticket->agent?->name ?? 'Unassigned' }}"
                            data-description="{{ $ticket->description }}"
                        >
                            #{{ $ticket->ticket_number }} — {{ $ticket->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ai-action-grid">
                <button type="button" class="ai-action-card active" data-mode="summary">
                    <span>Summary</span>
                    <strong>Generate Summary</strong>
                    <small>Create a short summary of the ticket conversation.</small>
                </button>

                <button type="button" class="ai-action-card" data-mode="reply">
                    <span>Reply</span>
                    <strong>Suggest Reply</strong>
                    <small>Draft a professional response for the customer.</small>
                </button>

                <button type="button" class="ai-action-card" data-mode="priority">
                    <span>Priority</span>
                    <strong>Detect Priority</strong>
                    <small>Estimate urgency based on the ticket content.</small>
                </button>
            </div>

            <div class="form-group full">
                <label for="prompt">Custom Prompt</label>
                <textarea
                    id="prompt"
                    rows="8"
                    placeholder="Example: Write a polite reply explaining that we are checking the billing issue..."
                ></textarea>
            </div>

            <div class="ai-generate-row">
                <button type="button" class="btn btn-primary" id="generateAiBtn">
                    Generate Response
                </button>
            </div>
        </div>
    </section>

    <aside class="card ai-output-card">
        <div class="ai-output-icon">AI</div>

        <h2>AI Output</h2>
        <p>The generated result will appear here after connecting the AI service.</p>

        <div class="ai-output-box" id="aiOutputBox">
            <strong>No output yet</strong>
            <span>Choose a ticket, write a prompt, then generate a response.</span>
        </div>

        <form action="{{ route('ai.useAsReply') }}" method="POST" id="aiUseReplyForm" class="ai-use-reply-form" style="display: none;">
        @csrf

        <input type="hidden" name="ticket_id" id="aiReplyTicketId">
        <input type="hidden" name="message" id="aiReplyMessage">
        <input type="hidden" name="is_internal_note" id="aiInternalNoteInput" value="0">

        <button type="submit" class="btn btn-primary" onclick="document.getElementById('aiInternalNoteInput').value = 0">
            Use as Reply
        </button>

        <button type="submit" class="btn btn-edit-soft" onclick="document.getElementById('aiInternalNoteInput').value = 1">
            Use as Internal Note
        </button>
    </form>
    </aside>
</div>

<script>
    (() => {
        const aiCards = document.querySelectorAll('.ai-action-card');
        const promptBox = document.getElementById('prompt');
        const ticketSelect = document.getElementById('ticket_id');
        const generateBtn = document.getElementById('generateAiBtn');
        const outputBox = document.getElementById('aiOutputBox');
        const useReplyForm = document.getElementById('aiUseReplyForm');
        const aiReplyTicketId = document.getElementById('aiReplyTicketId');
        const aiReplyMessage = document.getElementById('aiReplyMessage');

        let selectedMode = 'summary';
        let latestGeneratedText = '';

        const defaultPrompts = {
            summary: 'Summarize this ticket clearly for the support team.',
            reply: 'Write a professional and helpful reply to the customer.',
            priority: 'Analyze this ticket and suggest the correct priority.',
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getSelectedTicket() {
            const option = ticketSelect?.options[ticketSelect.selectedIndex];

            if (!option || !option.value) {
                return null;
            }

            return {
                id: option.value,
                number: option.dataset.number,
                title: option.dataset.title,
                status: option.dataset.status,
                priority: option.dataset.priority,
                department: option.dataset.department,
                requester: option.dataset.requester,
                agent: option.dataset.agent,
                description: option.dataset.description,
            };
        }

        function renderOutput(title, body, plainText = '') {
            outputBox.classList.add('has-output');
            outputBox.innerHTML = `
                <strong>${escapeHtml(title)}</strong>
                <div class="ai-generated-text">${body}</div>
            `;

            latestGeneratedText = plainText || outputBox.innerText.trim();

            const ticket = getSelectedTicket();

            if (ticket && useReplyForm && aiReplyTicketId && aiReplyMessage) {
                aiReplyTicketId.value = ticket.id;
                aiReplyMessage.value = latestGeneratedText;
                useReplyForm.style.display = 'flex';
            }
        }

        aiCards.forEach(card => {
            card.addEventListener('click', () => {
                aiCards.forEach(item => item.classList.remove('active'));
                card.classList.add('active');

                selectedMode = card.dataset.mode || 'summary';

                if (promptBox && !promptBox.value.trim()) {
                    promptBox.value = defaultPrompts[selectedMode] || '';
                }
            });
        });

        generateBtn?.addEventListener('click', () => {
            const ticket = getSelectedTicket();

            if (!ticket) {
                if (useReplyForm) {
                    useReplyForm.style.display = 'none';
                }

                renderOutput(
                    'Select a ticket first',
                    '<p>Please choose a ticket before generating an AI response.</p>'
                );
                return;
            }

            const number = escapeHtml(ticket.number);
            const title = escapeHtml(ticket.title);
            const requester = escapeHtml(ticket.requester);
            const department = escapeHtml(ticket.department);
            const status = escapeHtml(ticket.status);
            const priority = escapeHtml(ticket.priority);
            const description = escapeHtml(ticket.description || 'No description provided.');
            const customPrompt = escapeHtml(promptBox?.value.trim() || '');

            if (selectedMode === 'summary') {
                const summaryText = `Ticket: ${ticket.title}
Requester: ${ticket.requester}
Department: ${ticket.department}
Status: ${ticket.status}

This ticket is about: ${ticket.description || 'No description provided.'}

The support team should review the issue, confirm the current status, and respond with the next clear step.`;

                renderOutput(
                    `Summary for #${number}`,
                    `
                        <p><strong>Ticket:</strong> ${title}</p>
                        <p><strong>Requester:</strong> ${requester}</p>
                        <p><strong>Department:</strong> ${department}</p>
                        <p><strong>Status:</strong> ${status}</p>
                        <p>This ticket is about: ${description}</p>
                        <p>The support team should review the issue, confirm the current status, and respond with the next clear step.</p>
                    `,
                    summaryText
                );
            }

            if (selectedMode === 'reply') {
                const replyText = `Hello ${ticket.requester},

Thank you for contacting us. We have received your request regarding "${ticket.title}".

Our support team is currently reviewing the issue under the ${ticket.department} department. We will update you as soon as we have more details.

Best regards,
ResolveIQ Support Team`;

                renderOutput(
                    `Suggested Reply for #${number}`,
                    `
                        <p>Hello ${requester},</p>
                        <p>Thank you for contacting us. We have received your request regarding <strong>${title}</strong>.</p>
                        <p>Our support team is currently reviewing the issue under the <strong>${department}</strong> department. We will update you as soon as we have more details.</p>
                        <p>Best regards,<br>ResolveIQ Support Team</p>
                    `,
                    replyText
                );
            }

            if (selectedMode === 'priority') {
                const suggestedPriority = ['urgent', 'high'].includes(String(ticket.priority).toLowerCase())
                    ? 'High attention required'
                    : 'Normal handling';

                const priorityText = `Current priority: ${ticket.priority}
Suggested handling: ${suggestedPriority}

This result is based on the current ticket priority, department, and description. Later, this can be replaced with real AI analysis.`;

                renderOutput(
                    `Priority Analysis for #${number}`,
                    `
                        <p><strong>Current priority:</strong> ${priority}</p>
                        <p><strong>Suggested handling:</strong> ${escapeHtml(suggestedPriority)}</p>
                        <p>This result is based on the current ticket priority, department, and description. Later, this can be replaced with real AI analysis.</p>
                    `,
                    priorityText
                );
            }

            if (customPrompt) {
                outputBox.innerHTML += `
                    <div class="ai-prompt-used">
                        <strong>Prompt used:</strong>
                        <span>${customPrompt}</span>
                    </div>
                `;
            }

            if (aiReplyMessage) {
                aiReplyMessage.value = latestGeneratedText;
            }
        });
    })();
</script>
@endsection

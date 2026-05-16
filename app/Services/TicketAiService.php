<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Throwable;

class TicketAiService
{
    private const MAX_AI_ATTEMPTS = 3;

    private bool $usedFallback = false;

    public function usedFallback(): bool
    {
        return $this->usedFallback;
    }

    public function generate(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $this->usedFallback = false;

        $ticket->loadMissing([
            'user',
            'agent',
            'department',
            'replies.user',
            'activityLogs.user',
        ]);

        $provider = config('services.ai.provider', 'mock');

        if ($provider === 'openrouter') {
            return $this->generateWithOpenRouter($ticket, $type, $customPrompt);
        }

        if ($provider === 'openai') {
            return $this->generateWithOpenAi($ticket, $type, $customPrompt);
        }

        return $this->mockResponse($ticket, $type, $customPrompt);
    }

    private function generateWithOpenRouter(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $apiKey = config('services.openrouter.key');

        if (! $apiKey) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }

        $prompt = $this->buildPrompt($ticket, $type, $customPrompt);

        $payload = [
            'model' => config('services.openrouter.model', 'openrouter/free'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemInstruction(),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 1000,
            'temperature' => 0.25,
        ];

        for ($attempt = 1; $attempt <= self::MAX_AI_ATTEMPTS; $attempt++) {
            try {
                $request = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(35)
                    ->withHeaders([
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => config('app.name', 'ResolveIQ'),
                    ]);

                if (app()->environment('local')) {
                    $request = $request->withoutVerifying();
                }

                $response = $request->post('https://openrouter.ai/api/v1/chat/completions', $payload);

                if ($response->successful()) {
                    $text = $this->extractOpenRouterText($response->json());

                    if (filled($text)) {
                        return trim($text);
                    }
                }
            } catch (Throwable $e) {
                // Retry a few times, then use the local fallback response.
            }

            $this->pauseBeforeRetry($attempt);
        }

        return $this->mockResponse($ticket, $type, $customPrompt);
    }

    private function generateWithOpenAi(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }

        $prompt = $this->buildPrompt($ticket, $type, $customPrompt);

        $payload = [
            'model' => config('services.openai.model'),
            'instructions' => $this->systemInstruction(),
            'input' => $prompt,
            'max_output_tokens' => 1000,
            'temperature' => 0.25,
        ];

        for ($attempt = 1; $attempt <= self::MAX_AI_ATTEMPTS; $attempt++) {
            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(35)
                    ->post('https://api.openai.com/v1/responses', $payload);

                if ($response->successful()) {
                    $text = $this->extractOpenAiText($response->json());

                    if (filled($text)) {
                        return trim($text);
                    }
                }
            } catch (Throwable $e) {
                // Retry a few times, then use the local fallback response.
            }

            $this->pauseBeforeRetry($attempt);
        }

        return $this->mockResponse($ticket, $type, $customPrompt);
    }

    private function buildPrompt(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $priority = $ticket->priority ?? 'Not set';
        $departmentName = $ticket->department?->name ?? 'No department';
        $requesterName = $ticket->user?->name ?? 'Unknown';
        $agentName = $ticket->agent?->name ?? 'Unassigned';

        $createdAt = $this->formatTicketDate($ticket->created_at ?? null);
        $updatedAt = $this->formatTicketDate($ticket->updated_at ?? null);
        $dueAt = $this->formatTicketDate($ticket->due_at ?? null);
        $firstResponseAt = $this->formatTicketDate($ticket->first_response_at ?? null);
        $resolvedAt = $this->formatTicketDate($ticket->resolved_at ?? null);
        $closedAt = $this->formatTicketDate($ticket->closed_at ?? null);

        $replies = $ticket->replies
            ->sortByDesc('created_at')
            ->take(6)
            ->map(function ($reply) {
                $author = $reply->user?->name ?? 'Unknown';
                $noteType = $reply->is_internal_note ? 'Internal note' : 'Customer/support reply';
                $replyTime = $this->formatTicketDate($reply->created_at ?? null);

                return "- {$replyTime} | {$noteType} by {$author}: {$reply->message}";
            })
            ->implode("\n") ?: 'No replies yet.';

        $activityLogs = $ticket->activityLogs
            ->sortByDesc('created_at')
            ->take(8)
            ->map(function ($log) {
                $actor = $log->user?->name ?? 'System';
                $logTime = $this->formatTicketDate($log->created_at ?? null);
                $oldValue = filled($log->old_value) ? " | Old: {$log->old_value}" : '';
                $newValue = filled($log->new_value) ? " | New: {$log->new_value}" : '';

                return "- {$logTime} | {$log->action} by {$actor}{$oldValue}{$newValue}";
            })
            ->implode("\n") ?: 'No activity logs yet.';

        $base = <<<PROMPT
TICKET CONTEXT
Ticket Number: {$ticket->ticket_number}
Title: {$ticket->title}
Description: {$ticket->description}
Status: {$ticket->status}
Priority: {$priority}
Department: {$departmentName}
Requester: {$requesterName}
Assigned Agent: {$agentName}
Created At: {$createdAt}
Updated At: {$updatedAt}
Due Date: {$dueAt}
First Response At: {$firstResponseAt}
Resolved At: {$resolvedAt}
Closed At: {$closedAt}

RECENT REPLIES
{$replies}

RECENT ACTIVITY
{$activityLogs}
PROMPT;

        $customPrompt = filled($customPrompt) ? trim($customPrompt) : null;
        $hasCustomPrompt = filled($customPrompt);

        $task = match ($type) {
            'summary' => 'Create a concise internal ticket summary for the support team.
Default format when there are no additional user instructions:
1. Main issue:
2. Important context:
3. Current status:
4. Due date:
5. Recommended next step:
Do not write a customer-facing message unless the user specifically asks for it.',

            'reply' => 'Write a professional customer-facing support reply.
Default rules when there are no additional user instructions:
- Be clear and helpful.
- Do not mention internal notes.
- Do not say the issue is fixed unless the ticket status proves it.
- Include one clear next step.
- Keep it under 120 words unless the user asks otherwise.',

            'priority' => 'Suggest exactly one priority from: low, medium, high, urgent.
Default format when there are no additional user instructions:
Priority: [low/medium/high/urgent]
Reason: [one short reason]',

            'due_date' => 'Recommend one SLA-style due date based on ticket urgency, current priority, status, and context.
Default format when there are no additional user instructions:
Due Date: [YYYY-MM-DD]
Reason: [one short reason]',

            'custom' => 'Follow the custom instruction from the user and answer based only on this ticket context.
If the instruction asks for a customer reply, make it professional.
If it asks for analysis, keep it structured and practical.',

            default => 'Help analyze this support ticket.',
        };

        $outputPolicy = <<<PROMPT
OUTPUT POLICY
- Default language is English.
- If there are no additional user instructions, answer in English.
- If additional user instructions explicitly request another language, follow that language.
- If additional user instructions are written mainly in Arabic, answer in Arabic.
- Additional user instructions are mandatory when they are related to this ticket, support workflow, language, tone, length, structure, or required fields.
- Apply additional user instructions to every action type: Summary, Reply, Priority, Due Date, and Custom.
- When additional user instructions exist, treat them as the main request. The selected action is only the starting context, not a limitation.
- If additional user instructions ask for comments, replies, activity logs, due date, priority, status, table format, short output, detailed output, or Arabic, include that exactly when available in the ticket context.
- If the selected action is Priority, keep a parsable line like "Priority: medium" somewhere in the answer so the Apply Priority button can still work.
- If the selected action is Due Date, keep a parsable line like "Due Date: YYYY-MM-DD" somewhere in the answer so the Apply Due Date button can still work.
- If additional instructions conflict with the default format, follow the additional instructions and still keep the answer useful for this helpdesk ticket.
- Do not follow requests unrelated to helpdesk/ticket work. If the user asks for something unrelated, briefly explain that you can only help with this ticket.
- Do not invent facts that are not available in the ticket context.
PROMPT;

        $additionalInstructions = '';

        if ($hasCustomPrompt) {
            $additionalInstructions = <<<PROMPT

USER ADDITIONAL INSTRUCTIONS - HIGHEST PRIORITY
The user wrote the text below in the Additional instructions field. You must follow it for this request, even if the selected action is Summary, Reply, Priority, or Due Date.

User instructions:
{$customPrompt}

Before finalizing, verify that the answer follows the user instructions above. If the user asks for Arabic, the final answer must be Arabic. If the user asks for comments/replies/logs, include them from RECENT REPLIES or RECENT ACTIVITY.
PROMPT;
        }

        $finalAnswerRule = $hasCustomPrompt
            ? 'Write the final answer only. Follow USER ADDITIONAL INSTRUCTIONS - HIGHEST PRIORITY.'
            : 'Write the final answer only. Use the default task format. Do not explain your reasoning.';

        return <<<PROMPT
{$base}

SELECTED ACTION
{$task}

{$outputPolicy}{$additionalInstructions}

FINAL ANSWER
{$finalAnswerRule}
PROMPT;
    }

    private function systemInstruction(): string
    {
        return 'You are ResolveIQ AI Assistant inside a helpdesk system. Default language is English unless user additional instructions request another language or are mainly Arabic. User additional instructions inside the prompt are the highest priority for language, tone, length, structure, focus, and required fields when they are related to the current ticket or helpdesk workflow. Follow additional instructions for every mode, not only Custom. The selected action is a starting context, not a limitation. If the user asks for comments, replies, activity logs, due date, priority, status, or a specific format, include that information from the ticket context. For Priority mode keep a parsable Priority line; for Due Date mode keep a parsable Due Date line. Refuse unrelated requests briefly and redirect to ticket-related help. Do not invent facts outside the provided ticket context.';
    }

    private function pauseBeforeRetry(int $attempt): void
    {
        if ($attempt < self::MAX_AI_ATTEMPTS) {
            usleep(450000 * $attempt);
        }
    }

    private function extractOpenRouterText(array $data): ?string
    {
        $text = $data['choices'][0]['message']['content'] ?? null;

        return filled($text) ? trim($text) : null;
    }

    private function extractOpenAiText(array $data): ?string
    {
        if (! empty($data['output_text'])) {
            return trim($data['output_text']);
        }

        foreach (($data['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text') {
                    $text = $content['text'] ?? null;

                    return filled($text) ? trim($text) : null;
                }
            }
        }

        return null;
    }

    private function mockResponse(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $this->usedFallback = true;

        $requesterName = $ticket->user?->name ?? 'customer';
        $dueAt = $this->formatTicketDate($ticket->due_at ?? null);
        $suggestedDueDate = $this->suggestDueDate($ticket);

        if ($this->wantsArabic($customPrompt)) {
            return match ($type) {
                'summary' => "ملخص التذكرة {$ticket->ticket_number}:\n1. المشكلة الرئيسية: {$ticket->title}.\n2. السياق المهم: {$ticket->description}\n3. الحالة الحالية: {$ticket->status}.\n4. تاريخ الاستحقاق: {$dueAt}.\n5. الخطوة المقترحة: مراجعة المشكلة والرد على العميل بخطوات واضحة.",

                'reply' => "مرحبًا {$requesterName}،\n\nشكرًا لتواصلك مع فريق ResolveIQ Support. وصلتنا مشكلتك بخصوص \"{$ticket->title}\"، وسيقوم الفريق بمراجعتها ثم تزويدك بالخطوات التالية في أقرب وقت ممكن.\n\nمع التحية،\nفريق ResolveIQ Support",

                'priority' => "Priority: medium\nReason: التذكرة تحتاج إلى مراجعة، لكن لا توجد مؤشرات طارئة واضحة في التفاصيل الحالية.",

                'due_date' => "Due Date: {$suggestedDueDate}\nReason: تم اقتراح هذا التاريخ بناءً على أولوية التذكرة وحالتها الحالية.",

                'custom' => "استجابة مخصصة للتذكرة {$ticket->ticket_number}:\n" . ($customPrompt ?: 'اكتب تعليمات مخصصة للحصول على رد أدق.'),

                default => 'استجابة الذكاء الاصطناعي غير متاحة حاليًا.',
            };
        }

        return match ($type) {
            'summary' => "Ticket {$ticket->ticket_number} summary:\n1. Main issue: {$ticket->title}.\n2. Important context: {$ticket->description}\n3. Current status: {$ticket->status}.\n4. Due date: {$dueAt}.\n5. Recommended next step: review the issue and respond with clear instructions.",

            'reply' => "Hello {$requesterName},\n\nThank you for contacting ResolveIQ Support. We received your request about \"{$ticket->title}\". Our team is reviewing it and will follow up with the next steps.\n\nBest regards,\nResolveIQ Support Team",

            'priority' => "Priority: medium\nReason: The ticket requires review, but no immediate emergency indicators were detected.",

            'due_date' => "Due Date: {$suggestedDueDate}\nReason: This date was suggested based on the ticket priority and current status.",

            'custom' => "Custom AI response for ticket {$ticket->ticket_number}:\n" . ($customPrompt ?: 'Please provide a custom instruction to generate a more specific response.'),

            default => 'AI response is not available.',
        };
    }

    private function suggestDueDate(Ticket $ticket): string
    {
        if (! empty($ticket->due_at)) {
            try {
                $existingDueDate = Carbon::parse($ticket->due_at)->startOfDay();

                if ($existingDueDate->gte(Carbon::today())) {
                    return $existingDueDate->format('Y-m-d');
                }
            } catch (Throwable $e) {
                // Continue and calculate a new suggested due date.
            }
        }

        $priority = strtolower($ticket->priority ?? 'medium');

        $daysToAdd = match ($priority) {
            'urgent' => 1,
            'high' => 2,
            'medium' => 5,
            'low' => 7,
            default => 5,
        };

        return Carbon::now()
            ->addDays($daysToAdd)
            ->format('Y-m-d');
    }

    private function formatTicketDate($value): string
    {
        if (! $value) {
            return 'Not set';
        }

        try {
            return Carbon::parse($value)->format('M d, Y - h:i A');
        } catch (Throwable $e) {
            return (string) $value;
        }
    }

    private function wantsArabic(?string $customPrompt): bool
    {
        if (! $customPrompt) {
            return false;
        }

        $text = mb_strtolower($customPrompt);

        return str_contains($text, 'arabic')
            || str_contains($text, 'عربي')
            || str_contains($text, 'بالعربي')
            || str_contains($text, 'العربية')
            || preg_match('/\p{Arabic}/u', $customPrompt) === 1;
    }
}

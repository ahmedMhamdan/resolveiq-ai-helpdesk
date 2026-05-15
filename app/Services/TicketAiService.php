<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TicketAiService
{
    private bool $usedFallback = false;

    public function usedFallback(): bool
    {
        return $this->usedFallback;
    }

    public function generate(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $this->usedFallback = false;

        $ticket->loadMissing(['user', 'agent', 'department', 'replies.user', 'activityLogs.user']);

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
        if (! config('services.openrouter.key')) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }

        $prompt = $this->buildPrompt($ticket, $type, $customPrompt);

        try {
            $request = Http::withToken(config('services.openrouter.key'))
                ->acceptJson()
                ->timeout(30)
                ->withHeaders([
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name', 'ResolveIQ'),
                ]);

            // Local WAMP sometimes has SSL certificate issues. Do not use this in production.
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => config('services.openrouter.model', 'openrouter/free'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI assistant inside a helpdesk system. Be professional, concise, and useful for support agents. Always follow any additional user instruction in the prompt, especially requested language, tone, length, and formatting.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 500,
            ]);

            if ($response->failed()) {
                return $this->mockResponse($ticket, $type, $customPrompt);
            }

            return $this->extractOpenRouterText($response->json())
                ?: $this->mockResponse($ticket, $type, $customPrompt);
        } catch (ConnectionException|Throwable $e) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }
    }

    private function generateWithOpenAi(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        if (! config('services.openai.key')) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }

        $prompt = $this->buildPrompt($ticket, $type, $customPrompt);

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model'),
                    'instructions' => 'You are an AI assistant inside a helpdesk system. Be professional, concise, and useful for support agents. Always follow any additional user instruction in the prompt, especially requested language, tone, length, and formatting.',
                    'input' => $prompt,
                    'max_output_tokens' => 500,
                ]);

            if ($response->failed()) {
                return $this->mockResponse($ticket, $type, $customPrompt);
            }

            return $this->extractOpenAiText($response->json())
                ?: $this->mockResponse($ticket, $type, $customPrompt);
        } catch (ConnectionException|Throwable $e) {
            return $this->mockResponse($ticket, $type, $customPrompt);
        }
    }

    private function buildPrompt(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $priority = $ticket->priority ?? 'Not set';
        $departmentName = $ticket->department?->name ?? 'No department';
        $requesterName = $ticket->user?->name ?? 'Unknown';
        $agentName = $ticket->agent?->name ?? 'Unassigned';
        $createdAt = $this->formatTicketDate($ticket->created_at);
        $updatedAt = $this->formatTicketDate($ticket->updated_at);
        $dueAt = $this->formatTicketDate($ticket->due_at ?? null);
        $firstResponseAt = $this->formatTicketDate($ticket->first_response_at ?? null);
        $resolvedAt = $this->formatTicketDate($ticket->resolved_at ?? null);
        $closedAt = $this->formatTicketDate($ticket->closed_at ?? null);

        $replies = $ticket->replies
            ->take(6)
            ->map(function ($reply) {
                $author = $reply->user?->name ?? 'Unknown';
                $noteType = $reply->is_internal_note ? 'Internal note' : 'Customer/support reply';
                $replyTime = $this->formatTicketDate($reply->created_at ?? null);

                return "- {$replyTime} | {$noteType} by {$author}: {$reply->message}";
            })
            ->implode("\n");

        $activityLogs = $ticket->activityLogs
            ->sortBy('created_at')
            ->take(8)
            ->map(function ($log) {
                $actor = $log->user?->name ?? 'System';
                $logTime = $this->formatTicketDate($log->created_at ?? null);
                $oldValue = $log->old_value ? " | Old: {$log->old_value}" : '';
                $newValue = $log->new_value ? " | New: {$log->new_value}" : '';

                return "- {$logTime} | {$actor}: {$log->action}{$oldValue}{$newValue}";
            })
            ->implode("\n");

        $base = <<<PROMPT
Ticket Number: {$ticket->ticket_number}
Title: {$ticket->title}
Description: {$ticket->description}
Status: {$ticket->status}
Priority: {$priority}
Department: {$departmentName}
Requester: {$requesterName}
Assigned Agent: {$agentName}
Created At: {$createdAt}
Last Updated At: {$updatedAt}
Due At: {$dueAt}
First Response At: {$firstResponseAt}
Resolved At: {$resolvedAt}
Closed At: {$closedAt}

Recent Replies:
{$replies}

Activity Logs:
{$activityLogs}
PROMPT;

        $task = match ($type) {
            'summary' => 'Create a concise internal ticket summary using this exact format:
1. Main issue:
2. Important context:
3. Current status:
4. Recommended next step:
Do not write a customer-facing message.',

            'due_date' => 'Suggest exactly one due date for this ticket.
Rules:
- Return a real future date only.
- Use YYYY-MM-DD format.
- Consider priority, status, current due date, first response time, and ticket urgency.
- If the current due date is already reasonable, you may suggest keeping it.
Return this exact format only:
Due Date: YYYY-MM-DD
Reason: [one short reason]
If the user asks for Arabic, use:
تاريخ الاستحقاق: YYYY-MM-DD
السبب: [سبب قصير]',

            'reply' => 'Write a professional customer-facing support reply.
Rules:
- Be clear and helpful.
- Do not mention internal notes.
- Do not say the issue is fixed unless the ticket status proves it.
- Include one clear next step.
- Keep it under 120 words.',

            'priority' => 'Suggest exactly one priority from: low, medium, high, urgent.
Return this format only:
Priority: [low/medium/high/urgent]
Reason: [one short reason]',

            'custom' => 'Follow the custom instruction below and answer based only on this ticket context.
If the instruction asks for a customer reply, make it professional.
If it asks for analysis, keep it structured and practical.',

            default => 'Help analyze this support ticket.',
        };

        if ($customPrompt) {
            $task = "Important: Follow this additional instruction from the user. If it requests a language, tone, length, or format, obey it while staying within the ticket context.\n{$customPrompt}\n\n" . $task;
        }

        return $base . "\n\nTask:\n" . $task;
    }

    private function extractOpenRouterText(array $data): ?string
    {
        $text = $data['choices'][0]['message']['content'] ?? null;

        return $text ? trim($text) : null;
    }

    private function extractOpenAiText(array $data): ?string
    {
        if (! empty($data['output_text'])) {
            return trim($data['output_text']);
        }

        foreach (($data['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text') {
                    return trim($content['text'] ?? '');
                }
            }
        }

        return null;
    }

    private function mockResponse(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $this->usedFallback = true;

        $requesterName = $ticket->user?->name ?? 'customer';
        $wantsArabic = $this->wantsArabic($customPrompt);
        $createdAt = $this->formatTicketDate($ticket->created_at);
        $agentName = $ticket->agent?->name ?? ($wantsArabic ? 'غير معين' : 'Unassigned');
        $replyCount = $ticket->replies->count();
        $priority = $ticket->priority ?? ($wantsArabic ? 'غير محددة' : 'Not set');
        $suggestedDueDate = $this->suggestDueDate($ticket);

        if ($wantsArabic) {
            return match ($type) {
                'summary' => "ملخص التذكرة {$ticket->ticket_number}:\n1. المشكلة الرئيسية: {$ticket->title}.\n2. السياق المهم: {$ticket->description}\n3. الحالة الحالية: {$ticket->status}.\n4. الخطوة المقترحة: مراجعة المشكلة والرد على العميل بخطوات واضحة.",

                'due_date' => "تاريخ الاستحقاق: {$suggestedDueDate}
السبب: تم اقتراح هذا التاريخ بناءً على أولوية التذكرة وحالتها الحالية.",

                'reply' => "مرحبًا {$requesterName}،\n\nشكرًا لتواصلك مع فريق ResolveIQ Support. وصلتنا مشكلتك بخصوص \"{$ticket->title}\"، وسيقوم الفريق بمراجعتها ثم تزويدك بالخطوات التالية في أقرب وقت ممكن.\n\nمع التحية،\nفريق ResolveIQ Support",

                'priority' => "Priority: medium\nReason: التذكرة تحتاج إلى مراجعة، لكن لا توجد مؤشرات طارئة واضحة في التفاصيل الحالية.",

                'custom' => "استجابة مخصصة للتذكرة {$ticket->ticket_number}:\n" . ($customPrompt ?: 'اكتب تعليمات مخصصة للحصول على رد أدق.'),

                default => 'استجابة الذكاء الاصطناعي غير متاحة حاليًا.',
            };
        }

        return match ($type) {
            'summary' => "Ticket {$ticket->ticket_number} summary:\n1. Main issue: {$ticket->title}.\n2. Important context: {$ticket->description}\n3. Current status: {$ticket->status}.\n4. Recommended next step: review the issue and respond with clear instructions.",

            'due_date' => "Due Date: {$suggestedDueDate}
Reason: This date was suggested based on the ticket priority and current status.",

            'reply' => "Hello {$requesterName},\n\nThank you for contacting ResolveIQ Support. We received your request about \"{$ticket->title}\". Our team is reviewing it and will follow up with the next steps.\n\nBest regards,\nResolveIQ Support Team",

            'priority' => "Priority: medium\nReason: The ticket requires review, but no immediate emergency indicators were detected.",

            'custom' => "Custom AI response for ticket {$ticket->ticket_number}:\n" . ($customPrompt ?: 'Please provide a custom instruction to generate a more specific response.'),

            default => 'AI response is not available.',
        };
    }


    private function suggestDueDate(Ticket $ticket): string
    {
        if (! empty($ticket->due_at)) {
            try {
                $existingDueDate = \Illuminate\Support\Carbon::parse($ticket->due_at)->startOfDay();

                if ($existingDueDate->gte(\Illuminate\Support\Carbon::today())) {
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

        return \Illuminate\Support\Carbon::now()
            ->addDays($daysToAdd)
            ->format('Y-m-d');
    }

    private function formatTicketDate($value): string
    {
        if (! $value) {
            return 'Not available';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i');
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
            || str_contains($text, 'العربية');
    }
}

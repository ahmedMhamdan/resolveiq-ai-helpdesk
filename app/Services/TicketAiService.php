<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TicketAiService
{
    public function generate(Ticket $ticket, string $type, ?string $customPrompt = null): string
    {
        $ticket->loadMissing(['user', 'agent', 'department', 'replies.user']);

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
                        'content' => 'You are an AI assistant inside a helpdesk system. Be professional, concise, and useful for support agents.',
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
                    'instructions' => 'You are an AI assistant inside a helpdesk system. Be professional, concise, and useful for support agents.',
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

        $replies = $ticket->replies
            ->take(6)
            ->map(function ($reply) {
                $author = $reply->user?->name ?? 'Unknown';
                $noteType = $reply->is_internal_note ? 'Internal note' : 'Customer/support reply';

                return "- {$noteType} by {$author}: {$reply->message}";
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

Recent Replies:
{$replies}
PROMPT;

        $task = match ($type) {
            'summary' => 'Create a concise internal ticket summary using this exact format:
1. Main issue:
2. Important context:
3. Current status:
4. Recommended next step:
Do not write a customer-facing message.',

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
            $task .= "\n\nAdditional instruction from user:\n{$customPrompt}";
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
        $requesterName = $ticket->user?->name ?? 'customer';

        return match ($type) {
            'summary' => "Ticket {$ticket->ticket_number} summary:\n1. Main issue: {$ticket->title}.\n2. Important context: {$ticket->description}\n3. Current status: {$ticket->status}.\n4. Recommended next step: review the issue and respond with clear instructions.",

            'reply' => "Hello {$requesterName},\n\nThank you for contacting ResolveIQ Support. We received your request about \"{$ticket->title}\". Our team is reviewing it and will follow up with the next steps.\n\nBest regards,\nResolveIQ Support Team",

            'priority' => "Priority: medium\nReason: The ticket requires review, but no immediate emergency indicators were detected.",

            'custom' => "Custom AI response for ticket {$ticket->ticket_number}:\n" . ($customPrompt ?: 'Please provide a custom instruction to generate a more specific response.'),

            default => 'AI response is not available.',
        };
    }
}

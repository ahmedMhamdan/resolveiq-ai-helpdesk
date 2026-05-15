<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AiAssistantController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->roleName($user);

        abort_unless(in_array($role, ['admin', 'agent'], true), 403);

        $tickets = Ticket::query()
            ->with(['user', 'agent', 'department', 'replies.user'])
            ->where('status', '!=', 'closed')
            ->when($role === 'agent', function ($query) use ($user) {
                $query->where('agent_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        return view('ai-assistant.index', compact('tickets'));
    }

    public function generateForTicket(Request $request, Ticket $ticket, TicketAiService $aiService)
    {
        $user = $request->user();

        abort_unless($this->canUseTicketAi($user, $ticket), 403);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['summary', 'reply', 'priority', 'due_date', 'custom'])],
            'custom_prompt' => ['nullable', 'string', 'max:1500'],
        ]);

        if ($data['mode'] === 'custom' && empty(trim($data['custom_prompt'] ?? ''))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please write a custom instruction first.',
                    'errors' => [
                        'custom_prompt' => ['Please write a custom instruction first.'],
                    ],
                ], 422);
            }

            return back()
                ->withErrors(['custom_prompt' => 'Please write a custom instruction first.'])
                ->withInput();
        }

        $mode = $data['mode'];
        $body = $aiService->generate(
            $ticket,
            $mode,
            $data['custom_prompt'] ?? null
        );

        $suggestedPriority = null;
        $suggestedDueDate = null;

        if ($mode === 'priority') {
            $suggestedPriority = $this->extractSuggestedPriority($body);
        }

        if ($mode === 'due_date') {
            $suggestedDueDate = $this->extractSuggestedDueDate($body);
        }

        $ticketAi = [
            'ticket_id' => $ticket->id,
            'mode' => $mode,
            'title' => $this->makeAiTitle($mode, $ticket),
            'body' => $body,
            'suggested_priority' => $suggestedPriority,
            'suggested_due_date' => $suggestedDueDate,
            'used_fallback' => $aiService->usedFallback(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'AI suggestion generated successfully.',
                'ticket_ai' => $ticketAi,
            ]);
        }

        return redirect()
            ->route('ai.index', ['ticket_id' => $ticket->id])
            ->with('ticket_ai', $ticketAi)
            ->with('success', 'AI suggestion generated successfully.');
    }

    public function useTicketAiAsReply(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        abort_unless($this->canUseTicketAi($user, $ticket), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        return $this->storeAiReply($request, $ticket, $data['message']);
    }

    public function useAsReply(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($this->roleName($user), ['admin', 'agent'], true), 403);

        $data = $request->validate([
            'ticket_id' => ['required', 'exists:tickets,id'],
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        $ticket = Ticket::query()->findOrFail($data['ticket_id']);

        abort_unless($this->canUseTicketAi($user, $ticket), 403);

        return $this->storeAiReply($request, $ticket, $data['message']);
    }

    public function applyPriority(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        abort_unless($this->roleName($user) === 'admin', 403);

        $data = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $oldPriority = $ticket->priority;

        $ticket->fill([
            'priority' => $data['priority'],
        ]);

        $ticket->save();

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'AI priority applied',
            'old_value' => $oldPriority ?? 'Not set',
            'new_value' => $ticket->priority,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'AI suggested priority applied successfully.');
    }

    public function applyDueDate(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        abort_unless($this->roleName($user) === 'admin', 403);

        $data = $request->validate([
            'due_at' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $oldDueDate = $this->formatDateForLog($ticket->due_at);
        $newDueDate = Carbon::parse($data['due_at'])->endOfDay();

        $ticket->fill([
            'due_at' => $newDueDate,
        ]);

        $ticket->save();

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => 'AI due date applied',
            'old_value' => $oldDueDate ?? 'Not set',
            'new_value' => $newDueDate->format('Y-m-d'),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'AI suggested due date applied successfully.');
    }

    private function storeAiReply(Request $request, Ticket $ticket, string $message)
    {
        $user = $request->user();
        $isInternalNote = $request->boolean('is_internal_note');

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'message' => $message,
            'is_internal_note' => $isInternalNote,
        ]);

        if (! $isInternalNote && is_null($ticket->first_response_at)) {
            $ticket->fill([
                'first_response_at' => now(),
                'status' => $ticket->status === 'open' ? 'pending' : $ticket->status,
            ]);

            $ticket->save();
        }

        $ticket->activityLogs()->create([
            'user_id' => $user->id,
            'action' => $isInternalNote ? 'AI internal note added' : 'AI suggested reply used',
            'old_value' => null,
            'new_value' => $reply->message,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', $isInternalNote ? 'AI internal note added successfully.' : 'AI reply added successfully.');
    }

    private function canUseTicketAi($user, Ticket $ticket): bool
    {
        $role = $this->roleName($user);

        if ($role === 'admin') {
            return true;
        }

        return $role === 'agent' && (int) $ticket->agent_id === (int) $user->id;
    }

    private function roleName($user): string
    {
        return strtolower($user->role?->name ?? 'user');
    }

    private function makeAiTitle(string $mode, Ticket $ticket): string
    {
        return match ($mode) {
            'summary' => "AI Summary for {$ticket->ticket_number}",
            'reply' => "Suggested Reply for {$ticket->ticket_number}",
            'priority' => "Suggested Priority for {$ticket->ticket_number}",
            'due_date' => "Suggested Due Date for {$ticket->ticket_number}",
            'custom' => "Custom AI Response for {$ticket->ticket_number}",
            default => "AI Result for {$ticket->ticket_number}",
        };
    }

    private function extractSuggestedPriority(string $body): ?string
    {
        $text = strtolower($body);

        foreach (['urgent', 'high', 'medium', 'low'] as $priority) {
            if (str_contains($text, $priority)) {
                return $priority;
            }
        }

        return null;
    }


    private function extractSuggestedDueDate(string $body): ?string
    {
        $patterns = [
            '/(?:Due Date|Suggested Due Date)\s*:\s*(\d{4}-\d{2}-\d{2})/i',
            '/(?:تاريخ الاستحقاق|تاريخ الاستحقاق المقترح)\s*:\s*(\d{4}-\d{2}-\d{2})/u',
            '/\b(\d{4}-\d{2}-\d{2})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                try {
                    $date = Carbon::parse($matches[1])->startOfDay();

                    if ($date->gte(Carbon::today())) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    private function formatDateForLog($value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

}

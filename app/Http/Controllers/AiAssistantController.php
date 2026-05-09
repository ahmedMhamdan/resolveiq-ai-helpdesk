<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function index()
    {
        $tickets = Ticket::query()
            ->with(['user', 'agent', 'department', 'replies.user'])
            ->whereNotIn('status', ['closed'])
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        return view('ai-assistant.index', compact('tickets'));
    }

    public function useAsReply(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => ['required', 'exists:tickets,id'],
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        $ticket = Ticket::query()->findOrFail($data['ticket_id']);

        $actorId = User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'agent');
            })
            ->value('id');

        $isInternalNote = $request->boolean('is_internal_note');

        $reply = $ticket->replies()->create([
            'user_id' => $actorId,
            'message' => $data['message'],
            'is_internal_note' => $isInternalNote,
        ]);

        if (! $isInternalNote && is_null($ticket->first_response_at)) {
            $ticket->update([
                'first_response_at' => now(),
                'status' => $ticket->status === 'open' ? 'pending' : $ticket->status,
            ]);
        }

        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => $isInternalNote ? 'AI internal note added' : 'AI suggested reply used',
            'old_value' => null,
            'new_value' => $reply->message,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', $isInternalNote ? 'AI internal note added successfully.' : 'AI reply added successfully.');
    }
}

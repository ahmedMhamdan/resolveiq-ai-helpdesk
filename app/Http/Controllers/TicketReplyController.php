<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketReplyController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
        'message' => ['required', 'string', 'max:5000'],
        'is_internal_note' => ['nullable', 'boolean'],
        'attachments.*' => ['nullable', 'file', 'max:5120'],
        ]);

        $actorId = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })->value('id');

        $reply = $ticket->replies()->create([
        'user_id' => $actorId,
        'message' => $data['message'],
        'is_internal_note' => $request->boolean('is_internal_note'),
        ]);
        if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('ticket-attachments', 'public');

            $ticket->attachments()->create([
                'ticket_reply_id' => $reply->id,
                'user_id' => $actorId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
        if (! $reply->is_internal_note && is_null($ticket->first_response_at)) {
        $ticket->update([
            'first_response_at' => now(),
            'status' => $ticket->status === 'open' ? 'pending' : $ticket->status,
        ]);
        }
        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => $reply->is_internal_note ? 'Internal note added' : 'Reply added',
            'old_value' => null,
            'new_value' => $reply->is_internal_note ? 'internal_note' : 'reply',
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Reply added successfully.');
    }
    public function destroy(Ticket $ticket, TicketReply $reply)
    {
        if ($reply->ticket_id !== $ticket->id) {
            abort(404);
        }

        $actorId = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->value('id');

        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => $reply->is_internal_note ? 'Internal note deleted' : 'Reply deleted',
            'old_value' => $reply->message,
            'new_value' => null,
        ]);

        TicketReply::query()
            ->whereKey($reply->id)
            ->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Reply deleted successfully.');
    }
}

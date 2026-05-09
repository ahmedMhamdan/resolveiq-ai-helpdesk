<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function destroy(Ticket $ticket, TicketAttachment $attachment)
    {
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $actorId = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->value('id');

        $ticket->activityLogs()->create([
            'user_id' => $actorId,
            'action' => 'Attachment deleted',
            'old_value' => $attachment->file_name,
            'new_value' => null,
        ]);

        TicketAttachment::query()
            ->whereKey($attachment->id)
            ->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Attachment deleted successfully.');
    }
}

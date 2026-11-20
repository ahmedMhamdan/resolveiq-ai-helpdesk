<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function download(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        Gate::authorize('view', $ticket);

        abort_unless((int) $attachment->ticket_id === (int) $ticket->id, 404);
        abort_if($attachment->reply?->is_internal_note && ! $request->user()->isSupportStaff(), 404);

        $disk = Storage::disk(TicketAttachment::DISK);
        abort_unless($disk->exists($attachment->file_path), 404);

        return $disk->download($attachment->file_path, $attachment->file_name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        abort_unless($request->user()->roleName() === 'admin', 403);
        abort_unless((int) $attachment->ticket_id === (int) $ticket->id, 404);

        DB::transaction(function () use ($request, $ticket, $attachment) {
            $ticket->activityLogs()->create([
                'user_id' => $request->user()->id,
                'action' => 'Attachment deleted',
                'old_value' => $attachment->file_name,
                'new_value' => null,
            ]);

            $attachment->delete();
        });

        Storage::disk(TicketAttachment::DISK)->delete($attachment->file_path);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Attachment deleted successfully.');
    }
}

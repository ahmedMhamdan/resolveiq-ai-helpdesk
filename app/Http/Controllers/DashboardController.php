<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'open' => Ticket::query()
                ->where('status', '=', 'open')
                ->count(),

            'pending' => Ticket::query()
                ->where('status', '=', 'pending')
                ->count(),

            'solved' => Ticket::query()
                ->where('status', '=', 'solved')
                ->count(),

            'urgent' => Ticket::query()
                ->where('priority', '=', 'urgent')
                ->whereNotIn('status', ['solved', 'closed'])
                ->count(),
        ];

        $latestTickets = Ticket::query()
            ->with(['user', 'agent', 'department'])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $latestActivities = TicketActivityLog::query()
            ->with(['ticket', 'user'])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'stats',
            'latestTickets',
            'latestActivities'
        ));
    }
}

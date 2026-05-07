<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'open' => Ticket::query()->where('status', '=', 'open')->count(),
            'pending' => Ticket::query()->where('status', '=', 'pending')->count(),
            'solved' => Ticket::query()->where('status', '=', 'solved')->count(),
            'urgent' => Ticket::query()->where('priority', '=', 'urgent')->count(),
        ];

        $latestTickets = Ticket::query()
            ->with(['user', 'department'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact('stats', 'latestTickets'));
    }
}

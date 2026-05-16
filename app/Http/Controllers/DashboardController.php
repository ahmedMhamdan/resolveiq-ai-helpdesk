<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtolower($user->role?->name ?? 'user');
        $activitySearch = $request->query('activity_search');

        $ticketQuery = Ticket::query();

        if ($role === 'agent') {
            $ticketQuery->where('agent_id', $user->id);
        }

        if ($role === 'user') {
            $ticketQuery->where('user_id', $user->id);
        }

        $stats = [
            'open' => (clone $ticketQuery)
                ->where('status', 'open')
                ->count('*'),

            'pending' => (clone $ticketQuery)
                ->where('status', 'pending')
                ->count('*'),

            'solved' => (clone $ticketQuery)
                ->where('status', 'solved')
                ->count('*'),

            'urgent' => (clone $ticketQuery)
                ->where('priority', 'urgent')
                ->whereNotIn('status', ['solved', 'closed'], 'and')
                ->count('*'),
        ];

        $latestTickets = (clone $ticketQuery)
            ->with(['user', 'agent', 'department'])
            ->latest()
            ->take(6)
            ->get();

        $activityQuery = TicketActivityLog::query()
            ->with(['ticket', 'user']);

        if ($role === 'agent') {
            $activityQuery->whereHas('ticket', function ($query) use ($user) {
                $query->where('agent_id', $user->id);
            });
        }

        if ($role === 'user') {
            $activityQuery->whereHas('ticket', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        $activityQuery->when($activitySearch, function ($query, string $activitySearch) {
            $query->where(function ($query) use ($activitySearch) {
                $query->where('action', 'like', "%{$activitySearch}%")
                    ->orWhere('old_value', 'like', "%{$activitySearch}%")
                    ->orWhere('new_value', 'like', "%{$activitySearch}%")
                    ->orWhereHas('ticket', function ($ticketQuery) use ($activitySearch) {
                        $ticketQuery->where('ticket_number', 'like', "%{$activitySearch}%")
                            ->orWhere('title', 'like', "%{$activitySearch}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($activitySearch) {
                        $userQuery->where('name', 'like', "%{$activitySearch}%");
                    });
            });
        });

        $latestActivities = $activityQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'activity_page')
            ->withQueryString();

        return view('dashboard', compact(
            'stats',
            'latestTickets',
            'latestActivities',
            'user',
            'role',
            'activitySearch'
        ));
    }
}

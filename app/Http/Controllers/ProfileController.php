<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private function currentUser()
    {
        return User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })
            ->firstOrFail();
    }

    public function show()
    {
        $user = $this->currentUser();

        $assignedTicketsCount = Ticket::query()
            ->where('agent_id', $user->id)
            ->count();

        $repliesCount = TicketReply::query()
            ->where('user_id', $user->id)
            ->count();

        $latestReplies = TicketReply::query()
            ->with('ticket')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('profile.show', compact(
            'user',
            'assignedTicketsCount',
            'repliesCount',
            'latestReplies'
        ));
    }

    public function edit()
    {
        $user = $this->currentUser();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $this->currentUser();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}

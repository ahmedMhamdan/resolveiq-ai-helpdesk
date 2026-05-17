<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private function currentUser(Request $request)
    {
        return $request->user();
    }

    public function show(Request $request)
    {
        $user = $this->currentUser($request);

        $assignedTicketsCount = Ticket::query()
            ->where('agent_id', $user->id)
            ->count('id');

        $repliesCount = TicketReply::query()
            ->where('user_id', $user->id)
            ->count('id');

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

    public function edit(Request $request)
    {
        $user = $this->currentUser($request);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $this->currentUser($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['avatar']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && str_starts_with($user->avatar_path, 'avatars/')) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::query()
            ->whereHas('role', function (Builder $query) {
                $query->where('name', 'agent');
            })
            ->withCount(['assignedTickets', 'ticketReplies'])
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        return view('agents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:6'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $agentRole = Role::query()
            ->where('name', 'agent')
            ->firstOrFail();

        $agent = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $agentRole->id,
        ]);

        if ($request->hasFile('avatar')) {
            $agent->avatar_path = $this->uploadAvatar($request, $agent, 'agent');
            $agent->save();
        }

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent created successfully.');
    }

    public function edit(User $agent)
    {
        $this->abortIfNotAgent($agent);

        return view('agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
        $this->abortIfNotAgent($agent);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique(User::class, 'email')->ignore($agent->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $agent->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $agent->password = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            $this->deleteUploadedAvatar($agent);
            $agent->avatar_path = $this->uploadAvatar($request, $agent, 'agent');
        }

        $agent->save();

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent updated successfully.');
    }

    public function makeUser(User $agent)
    {
        $this->abortIfNotAgent($agent);

        if (auth()->id() === $agent->id) {
            return redirect()
                ->route('agents.index')
                ->with('error', 'You cannot change your own role.');
        }

        $userRole = Role::query()
            ->where('name', 'user')
            ->firstOrFail();

        Ticket::query()
            ->where('agent_id', $agent->id)
            ->update([
                'agent_id' => null,
            ]);

        $agent->update([
            'role_id' => $userRole->id,
        ]);

        return redirect()
            ->route('agents.index')
            ->with('success', $agent->name . ' moved back to Users successfully.');
    }

    public function destroy(User $agent)
    {
        $this->abortIfNotAgent($agent);

        $hasTickets = Ticket::query()
            ->where('agent_id', $agent->id)
            ->exists();

        $hasReplies = TicketReply::query()
            ->where('user_id', $agent->id)
            ->exists();

        if ($hasTickets || $hasReplies) {
            return redirect()
                ->route('agents.index')
                ->with('error', 'Cannot delete an agent assigned to tickets or replies.');
        }

        $this->deleteUploadedAvatar($agent);

        User::query()
            ->whereKey($agent->id)
            ->delete();

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent deleted successfully.');
    }

    private function uploadAvatar(Request $request, User $agent, string $prefix): string
    {
        $directory = public_path('images/avatars/uploads');

        File::ensureDirectoryExists($directory);

        $avatar = $request->file('avatar');
        $fileName = $prefix . '-' . $agent->id . '-' . time() . '.' . $avatar->getClientOriginalExtension();

        $avatar->move($directory, $fileName);

        return 'images/avatars/uploads/' . $fileName;
    }

    private function deleteUploadedAvatar(User $agent): void
    {
        if (! $agent->avatar_path || ! str_starts_with($agent->avatar_path, 'images/avatars/uploads/')) {
            return;
        }

        File::delete(public_path($agent->avatar_path));
    }

    private function abortIfNotAgent(User $user): void
    {
        $isAgent = $user->role()
            ->where('name', 'agent')
            ->exists();

        abort_unless($isAgent, 404);
    }
}

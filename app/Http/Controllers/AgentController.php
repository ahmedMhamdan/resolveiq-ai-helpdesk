<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
        ]);

        $agentRole = Role::query()
            ->where('name', 'agent')
            ->firstOrFail();

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $agentRole->id,
        ]);

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
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $agent->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $agent->password = Hash::make($data['password']);
        }

        $agent->save();

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent updated successfully.');
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

        User::query()
            ->whereKey($agent->id)
            ->delete();

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent deleted successfully.');
    }

    private function abortIfNotAgent(User $user): void
    {
        $isAgent = $user->role()
            ->where('name', 'agent')
            ->exists();

        abort_unless($isAgent, 404);
    }
}

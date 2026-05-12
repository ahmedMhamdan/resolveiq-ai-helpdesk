<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::query()
            ->with('role')
            ->withCount(['tickets', 'assignedTickets'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'agent'])],
        ]);

        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $role = Role::query()
            ->where('name', $data['role'])
            ->firstOrFail();

        $user->update([
            'role_id' => $role->id,
        ]);

        return back()->with('success', 'User role updated successfully.');
    }
}

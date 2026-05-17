<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::query()
            ->with('role')
            ->withCount(['tickets'])
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['user', 'admin']);
            })
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

    public function show(User $user)
    {
        $user->load('role')
            ->loadCount([
                'tickets',
                'assignedTickets',
                'ticketReplies',
                'ticketActivityLogs',
            ]);

        $createdTickets = $user->tickets()
            ->with(['department', 'agent'])
            ->latest()
            ->take(5)
            ->get();

        $assignedTickets = $user->assignedTickets()
            ->with(['department', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $recentReplies = $user->ticketReplies()
            ->with('ticket')
            ->latest()
            ->take(5)
            ->get();

        $activityLogs = $user->ticketActivityLogs()
            ->with('ticket')
            ->latest()
            ->take(5)
            ->get();

        return view('users.show', compact(
            'user',
            'createdTickets',
            'assignedTickets',
            'recentReplies',
            'activityLogs'
        ));
    }

    public function edit(Request $request, User $user)
    {
        $roleName = strtolower($user->role?->name ?? 'user');

        if ($roleName === 'admin' && $request->user()->id !== $user->id) {
            return redirect()
                ->route('users.show', $user)
                ->with('error', 'Protected admin account cannot be edited from Users Management.');
        }

        if ($roleName === 'agent') {
            return redirect()
                ->route('agents.edit', $user)
                ->with('error', 'Agents are managed from the Agents page.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $roleName = strtolower($user->role?->name ?? 'user');

        if ($roleName === 'admin' && $request->user()->id !== $user->id) {
            return redirect()
                ->route('users.show', $user)
                ->with('error', 'Protected admin account cannot be edited from Users Management.');
        }

        if ($roleName === 'agent') {
            return redirect()
                ->route('agents.edit', $user)
                ->with('error', 'Agents are managed from the Agents page.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $directory = public_path('images/avatars/uploads');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            if ($user->avatar_path && str_starts_with($user->avatar_path, 'images/avatars/uploads/')) {
                $oldAvatarPath = public_path($user->avatar_path);

                if (File::exists($oldAvatarPath)) {
                    File::delete($oldAvatarPath);
                }
            }

            $fileName = 'user-' . $user->id . '-' . time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move($directory, $fileName);

            $data['avatar_path'] = 'images/avatars/uploads/' . $fileName;
        }

        unset($data['avatar']);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data);
        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'agent'])],
        ]);

        $currentUser = $request->user();
        $currentRoleName = strtolower($user->role?->name ?? 'user');

        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        if ($currentRoleName === 'admin') {
            return back()->with('error', 'Admin accounts are protected.');
        }

        if ($currentRoleName === 'agent') {
            return redirect()
                ->route('agents.index')
                ->with('error', 'Agents are managed from the Agents page.');
        }

        $role = Role::query()
            ->where('name', $data['role'])
            ->firstOrFail();

        $user->role_id = $role->id;
        $user->save();

        return back()->with('success', 'User role updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);

        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function update(Request $request): JsonResponse
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

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && str_starts_with($user->avatar_path, 'avatars/')) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($data['avatar']);

        $user->fill($data);
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    private function currentUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function formatUser(User $user): array
    {
        $user->loadMissing('role');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name ?? 'user',
            'avatar_url' => $user->avatarUrl(),
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}

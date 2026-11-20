<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SaveUserProfile
{
    public function __invoke(User $user, array $data): void
    {
        $avatar = $data['avatar'] ?? null;
        unset($data['avatar']);

        $previousAvatar = $user->avatar_path;
        $newAvatar = null;

        if ($avatar) {
            $newAvatar = $avatar->store('avatars', 'public');

            if ($newAvatar === false) {
                throw new RuntimeException('Failed to store profile photo.');
            }

            $data['avatar_path'] = $newAvatar;
        }

        $user->fill($data);
        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        try {
            $user->save();
        } catch (Throwable $exception) {
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        if ($newAvatar && str_starts_with($previousAvatar ?? '', 'avatars/')) {
            Storage::disk('public')->delete($previousAvatar);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }
    }
}

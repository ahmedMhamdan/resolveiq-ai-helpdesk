<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): Response
    {
        $redirectUrl = route('login');

        if (! $request->hasValidSignature()) {
            return response()->view('auth.email-verification-result', [
                'status' => 'error',
                'title' => 'Verification link expired',
                'message' => 'This email verification link is invalid or expired. Please login and request a new verification email.',
                'buttonText' => 'Back to Login',
                'redirectUrl' => $redirectUrl,
            ], 403);
        }

        $user = User::query()->findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->view('auth.email-verification-result', [
                'status' => 'error',
                'title' => 'Invalid verification link',
                'message' => 'This verification link does not match your account. Please login and request a new one.',
                'buttonText' => 'Back to Login',
                'redirectUrl' => $redirectUrl,
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return response()->view('auth.email-verification-result', [
            'status' => 'success',
            'title' => 'Email verified successfully',
            'message' => 'Your ResolveIQ account is now verified. You will be redirected to the login page in a few seconds.',
            'buttonText' => 'Continue to Login',
            'redirectUrl' => $redirectUrl,
        ]);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationNotificationController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $user = User::where('email', mb_strtolower(trim($data['email'])))->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (TransportExceptionInterface $exception) {
                Log::warning('Student verification email resend failed.', [
                    'user_id' => $user->id,
                    'exception_class' => $exception::class,
                ]);

                return back()->withErrors([
                    'email' => 'The verification email could not be sent right now. Please try again shortly.',
                ])->onlyInput('email');
            }
        }

        return back()->with('status', 'verification-link-sent');
    }
}

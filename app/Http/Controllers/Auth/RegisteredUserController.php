<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'student_id' => $request->validated('student_id'),
            'password' => $request->validated('password'),
            'role' => 'student',
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Student verification email delivery failed.', [
                'user_id' => $user->id,
                'exception_class' => $exception::class,
            ]);

            return redirect()->route('verification.notice')->with('status', 'registration-email-failed');
        }

        return redirect()->route('verification.notice')->with('status', 'registration-created');
    }
}

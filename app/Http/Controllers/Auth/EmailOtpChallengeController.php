<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\EmailOtp;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Contracts\LoginResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EmailOtpChallengeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $this->challengedUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/EmailOtpChallenge', [
            'maskedEmail' => $this->maskEmail($user->email),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request): SymfonyResponse
    {
        $user = $this->challengedUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        if (! EmailOtp::verify($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('The provided code is invalid or has expired.'),
            ]);
        }

        Auth::login($user, (bool) $request->session()->pull('login.remember', false));

        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return app(LoginResponse::class)->toResponse($request);
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->challengedUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        EmailOtp::send($user);

        return back()->with('status', 'A new code has been sent to your email.');
    }

    private function challengedUser(Request $request): ?User
    {
        $id = $request->session()->get('login.id');

        if (! is_int($id) && ! is_string($id)) {
            return null;
        }

        return User::query()->find($id);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        return Str::mask($local, '•', 1).'@'.$domain;
    }
}

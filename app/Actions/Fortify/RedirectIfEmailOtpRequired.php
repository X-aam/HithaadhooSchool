<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Throwable;

class RedirectIfEmailOtpRequired extends RedirectIfTwoFactorAuthenticatable
{
    /**
     * Redirect password logins without an authenticator app to an emailed
     * one-time code challenge. Runs after the TOTP redirect pipe, so any
     * user reaching it has no confirmed authenticator app.
     *
     * @param  Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (! config('fortify.email_otp')) {
            return $next($request);
        }

        $user = $this->validateCredentials($request);

        if (! $user instanceof User || $user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        try {
            EmailOtp::send($user);
        } catch (Throwable $e) {
            // Fail open: a broken mailer must not lock everyone out of the site.
            Log::error('Email OTP could not be sent; skipping challenge.', ['user' => $user->getKey(), 'error' => $e->getMessage()]);

            return $next($request);
        }

        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->boolean('remember'),
        ]);

        return $request->wantsJson()
            ? response()->json(['email_otp' => true])
            : redirect()->route('email-otp.login');
    }
}

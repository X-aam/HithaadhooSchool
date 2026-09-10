<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\EmailOtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class EmailOtp
{
    private const TTL_MINUTES = 10;

    /**
     * Generate a fresh one-time code, store its hash and email it to the user.
     */
    public static function send(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(self::cacheKey($user), Hash::make($code), now()->addMinutes(self::TTL_MINUTES));

        $user->notify(new EmailOtpCode($code, self::TTL_MINUTES));
    }

    /**
     * Verify a submitted code, consuming it on success.
     */
    public static function verify(User $user, string $code): bool
    {
        $hash = Cache::get(self::cacheKey($user));

        if (! is_string($hash) || ! Hash::check($code, $hash)) {
            return false;
        }

        Cache::forget(self::cacheKey($user));

        return true;
    }

    private static function cacheKey(User $user): string
    {
        return "email-otp:{$user->getKey()}";
    }
}

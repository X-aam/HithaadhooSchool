<?php

use App\Actions\Fortify\EmailOtp;
use App\Models\User;
use App\Notifications\EmailOtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function challengeUser(User $user): void
{
    test()->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);
}

test('email otp challenge screen can be rendered after login', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    $response = $this->get(route('email-otp.login'));

    $response->assertOk();
});

test('email otp challenge screen redirects guests without a pending login', function () {
    $response = $this->get(route('email-otp.login'));

    $response->assertRedirect(route('login'));
});

test('users can complete login with a valid email otp code', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    Cache::put("email-otp:{$user->id}", Hash::make('123456'), 600);

    $response = $this->post(route('email-otp.login.store'), ['code' => '123456']);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect();
});

test('an email otp code cannot be reused', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    Cache::put("email-otp:{$user->id}", Hash::make('123456'), 600);

    expect(EmailOtp::verify($user, '123456'))->toBeTrue()
        ->and(EmailOtp::verify($user, '123456'))->toBeFalse();
});

test('users cannot login with an invalid email otp code', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    Cache::put("email-otp:{$user->id}", Hash::make('123456'), 600);

    $response = $this->post(route('email-otp.login.store'), ['code' => '000000']);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

test('users cannot login with an expired email otp code', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    Cache::forget("email-otp:{$user->id}");

    $response = $this->post(route('email-otp.login.store'), ['code' => '123456']);

    $response->assertSessionHasErrors('code');
    $this->assertGuest();
});

test('users can request a new email otp code', function () {
    Notification::fake();

    $user = User::factory()->create();
    challengeUser($user);

    $this->post(route('email-otp.resend'));

    Notification::assertSentToTimes($user, EmailOtpCode::class, 2);
});

test('login fails open when the otp email cannot be sent', function () {
    Notification::shouldReceive('send')->andThrow(new RuntimeException('mailer down'));

    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect();
});

<?php

use App\Enums\UserRole;
use App\Http\Responses\LoginResponse;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

function redirectAfterLoginFor(User $user): string
{
    $request = Request::create('/staff-portal-h7k2/login', 'POST');
    $request->setUserResolver(fn () => $user);

    return app(LoginResponse::class)->toResponse($request)->getTargetUrl();
}

test('signing in lands in the cms, not a team dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Editor]);

    // The team still exists; we just no longer land on its generated slug.
    expect($user->personalTeam())->not->toBeNull()
        ->and(redirectAfterLoginFor($user))->toEndWith('/admin');
});

test('a user with no team can still sign in', function () {
    // Accounts created outside the team flow — artisan, tinker, seeding — have
    // no team. The redirect must not abort on them.
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $user->teams()->detach();
    Team::query()->delete();
    $user->forceFill(['current_team_id' => null])->save();

    expect(redirectAfterLoginFor($user->fresh()))->toEndWith('/admin');
});

test('password logins are sent to the email code challenge first', function () {
    $user = User::factory()->create([
        'role' => UserRole::Editor,
        'password' => Hash::make('password-for-testing'),
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password-for-testing',
    ])->assertRedirect(route('email-otp.login'));
});

<?php

use App\Actions\Mail\MailOAuthTokens;
use App\Enums\UserRole;
use App\Models\MailSetting;
use App\Models\User;
use App\Support\MailOAuthProvider;
use Illuminate\Support\Facades\Http;

function mailAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function oauthSettings(array $overrides = []): MailSetting
{
    return MailSetting::store(array_merge([
        'mailer' => 'smtp',
        'auth_type' => 'microsoft',
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => 'tls',
        'from_address' => 'noreply@school.edu.mv',
        'from_name' => 'Hithaadhoo School',
        'verify_peer' => true,
        'oauth_client_id' => 'client-id-123',
        'oauth_client_secret' => 'client-secret-456',
        'oauth_email' => 'noreply@school.edu.mv',
    ], $overrides));
}

test('connecting sends the administrator to the provider with the right scopes', function () {
    oauthSettings();

    $response = $this->actingAs(mailAdmin())->get('/settings/mail/connect');

    $target = $response->assertRedirect()->headers->get('Location');

    expect($target)->toStartWith('https://login.microsoftonline.com/common/oauth2/v2.0/authorize')
        ->and($target)->toContain('client_id=client-id-123')
        // offline_access is what makes a refresh token come back.
        ->and(urldecode($target))->toContain('offline_access https://outlook.office.com/SMTP.Send')
        ->and(urldecode($target))->toContain(route('mail.oauth.callback'));
});

test('a google connection uses google endpoints and asks for offline access', function () {
    oauthSettings(['auth_type' => 'google', 'host' => 'smtp.gmail.com']);

    $target = $this->actingAs(mailAdmin())
        ->get('/settings/mail/connect')
        ->headers->get('Location');

    expect($target)->toStartWith('https://accounts.google.com/o/oauth2/v2/auth')
        ->and($target)->toContain('access_type=offline')
        ->and(urldecode($target))->toContain('https://mail.google.com/');
});

test('a microsoft tenant is used when one is set', function () {
    oauthSettings(['oauth_tenant' => 'contoso.onmicrosoft.com']);

    expect($this->actingAs(mailAdmin())->get('/settings/mail/connect')->headers->get('Location'))
        ->toStartWith('https://login.microsoftonline.com/contoso.onmicrosoft.com/oauth2/v2.0/authorize');
});

test('connecting is refused until the client details are saved', function () {
    MailSetting::store([
        'mailer' => 'smtp',
        'auth_type' => 'microsoft',
        'host' => 'smtp.office365.com',
        'port' => 587,
        'from_address' => 'a@b.com',
        'from_name' => 'X',
    ]);

    $this->actingAs(mailAdmin())
        ->get('/settings/mail/connect')
        ->assertRedirect(route('mail.edit'));

    expect(session('toast')['type'])->toBe('error');
});

test('the callback stores the refresh token', function () {
    Http::fake([
        '*/oauth2/v2.0/token' => Http::response([
            'access_token' => 'access-token-1',
            'refresh_token' => 'refresh-token-1',
            'expires_in' => 3600,
        ]),
    ]);

    oauthSettings();

    $this->actingAs(mailAdmin())
        ->withSession(['mail-oauth-state' => 'the-state'])
        ->get('/settings/mail/callback?code=auth-code&state=the-state')
        ->assertRedirect(route('mail.edit'));

    $settings = MailSetting::active();

    expect($settings->isConnected())->toBeTrue()
        ->and($settings->oauth_refresh_token)->toBe('refresh-token-1')
        ->and($settings->oauth_access_token)->toBe('access-token-1');
});

test('a callback with a mismatched state is rejected', function () {
    Http::fake();
    oauthSettings();

    $this->actingAs(mailAdmin())
        ->withSession(['mail-oauth-state' => 'the-real-state'])
        ->get('/settings/mail/callback?code=auth-code&state=a-forged-state')
        ->assertRedirect(route('mail.edit'));

    expect(MailSetting::active()->isConnected())->toBeFalse();
    Http::assertNothingSent();
});

test('a provider that returns no refresh token is reported, not silently accepted', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 'access-only', 'expires_in' => 3600]),
    ]);

    oauthSettings();

    $this->actingAs(mailAdmin())
        ->withSession(['mail-oauth-state' => 's'])
        ->get('/settings/mail/callback?code=c&state=s');

    expect(MailSetting::active()->isConnected())->toBeFalse()
        ->and(session('toast')['type'])->toBe('error')
        ->and(session('toast')['message'])->toContain('refresh token');
});

test('the provider error description is surfaced', function () {
    Http::fake([
        '*/token' => Http::response([
            'error' => 'invalid_client',
            'error_description' => "AADSTS7000215: Invalid client secret provided.\r\nTrace ID: abc",
        ], 401),
    ]);

    oauthSettings();

    $this->actingAs(mailAdmin())
        ->withSession(['mail-oauth-state' => 's'])
        ->get('/settings/mail/callback?code=c&state=s');

    expect(session('toast')['message'])->toContain('Invalid client secret provided.')
        // Trace ids are noise for the administrator reading the toast.
        ->and(session('toast')['message'])->not->toContain('Trace ID');
});

test('an expired access token is refreshed before use', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 'fresh-token', 'expires_in' => 3600]),
    ]);

    $settings = oauthSettings();
    $settings->forceFill([
        'oauth_refresh_token' => 'refresh-token-1',
        'oauth_access_token' => 'stale-token',
        'oauth_expires_at' => now()->subMinute(),
    ])->save();

    expect(app(MailOAuthTokens::class)->accessToken($settings->fresh()))->toBe('fresh-token');
});

test('a token still in date is reused without calling the provider', function () {
    Http::fake();

    $settings = oauthSettings();
    $settings->forceFill([
        'oauth_refresh_token' => 'refresh-token-1',
        'oauth_access_token' => 'good-token',
        'oauth_expires_at' => now()->addHour(),
    ])->save();

    expect(app(MailOAuthTokens::class)->accessToken($settings->fresh()))->toBe('good-token');
    Http::assertNothingSent();
});

test('google keeps the original refresh token when it sends none back', function () {
    Http::fake([
        '*/token' => Http::response(['access_token' => 'new-access', 'expires_in' => 3600]),
    ]);

    $settings = oauthSettings(['auth_type' => 'google']);
    $settings->forceFill([
        'oauth_refresh_token' => 'the-long-lived-token',
        'oauth_expires_at' => now()->subMinute(),
    ])->save();

    app(MailOAuthTokens::class)->refresh($settings->fresh());

    expect(MailSetting::active()->oauth_refresh_token)->toBe('the-long-lived-token');
});

test('disconnecting forgets the tokens but keeps the configuration', function () {
    $settings = oauthSettings();
    $settings->forceFill(['oauth_refresh_token' => 'r', 'oauth_access_token' => 'a'])->save();

    $this->actingAs(mailAdmin())->delete('/settings/mail/connect')->assertRedirect();

    $fresh = MailSetting::active();

    expect($fresh->isConnected())->toBeFalse()
        ->and($fresh->oauth_client_id)->toBe('client-id-123')
        ->and($fresh->host)->toBe('smtp.office365.com');
});

test('an oauth mailbox is not usable until it is connected', function () {
    $settings = oauthSettings();

    expect($settings->isUsable())->toBeFalse();

    $settings->forceFill(['oauth_refresh_token' => 'r'])->save();

    expect($settings->fresh()->isUsable())->toBeTrue();
});

test('oauth config sends the mailbox as the username and no stored password', function () {
    $settings = oauthSettings();
    $settings->forceFill(['oauth_refresh_token' => 'r'])->save();

    $config = $settings->fresh()->toConfig();

    expect($config['mail.mailers.smtp.username'])->toBe('noreply@school.edu.mv')
        ->and($config['mail.mailers.smtp.password'])->toBeNull()
        ->and($config['mail.mailers.smtp.oauth'])->toBe('microsoft');
});

test('switching provider clears consent granted to the old one', function () {
    $settings = oauthSettings();
    $settings->forceFill(['oauth_refresh_token' => 'microsoft-token'])->save();

    $this->actingAs(mailAdmin())->put('/settings/mail', [
        'mailer' => 'smtp',
        'auth_type' => 'google',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'from_address' => 'noreply@school.edu.mv',
        'from_name' => 'Hithaadhoo School',
        'oauth_client_id' => 'google-client',
        'oauth_email' => 'noreply@school.edu.mv',
    ])->assertRedirect();

    expect(MailSetting::active()->isConnected())->toBeFalse();
});

test('secrets and tokens are never sent to the browser', function () {
    $settings = oauthSettings();
    $settings->forceFill(['oauth_refresh_token' => 'super-secret-refresh'])->save();

    $this->actingAs(mailAdmin())
        ->get('/settings/mail')
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = json_encode($page->toArray()['props']);

            expect($props)->not->toContain('super-secret-refresh')
                ->and($props)->not->toContain('client-secret-456')
                ->and($page->toArray()['props']['hasClientSecret'])->toBeTrue()
                ->and($page->toArray()['props']['isConnected'])->toBeTrue();
        });
});

test('non-admins cannot start or undo a connection', function () {
    oauthSettings();
    $editor = User::factory()->create(['role' => UserRole::Editor]);

    $this->actingAs($editor)->get('/settings/mail/connect')->assertForbidden();
    $this->actingAs($editor)->delete('/settings/mail/connect')->assertForbidden();
});

test('every provider advertises a console url for registering the app', function () {
    foreach (MailOAuthProvider::options() as $option) {
        expect($option['console'])->toStartWith('https://')
            ->and($option['label'])->not->toBeEmpty();
    }
});

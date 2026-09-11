<?php

use App\Enums\UserRole;
use App\Models\MailSetting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Mail;

function admin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

function validSettings(array $overrides = []): array
{
    return array_merge([
        'mailer' => 'smtp',
        'host' => 'smtp.example.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'mail@example.com',
        'password' => 'secret-app-password',
        'from_address' => 'noreply@example.com',
        'from_name' => 'Hithaadhoo School',
        'verify_peer' => true,
    ], $overrides);
}

test('admins can view the email settings page', function () {
    $this->actingAs(admin())->get('/settings/mail')->assertOk();
});

test('non-admins cannot change the email settings', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);

    $this->actingAs($editor)->put('/settings/mail', validSettings())->assertForbidden();
    $this->actingAs($editor)->post('/settings/mail/test', ['email' => 'a@b.com'])->assertForbidden();
});

test('guests cannot reach the email settings', function () {
    // 404 rather than a redirect: the login path is deliberately obscure.
    $this->get('/settings/mail')->assertNotFound();
});

test('admins can save the mail server settings', function () {
    $this->actingAs(admin())->put('/settings/mail', validSettings())->assertRedirect();

    $row = MailSetting::query()->sole();

    expect($row->host)->toBe('smtp.example.com')
        ->and($row->port)->toBe(587)
        ->and($row->from_address)->toBe('noreply@example.com')
        ->and($row->password)->toBe('secret-app-password');
});

test('the smtp password is encrypted at rest', function () {
    $this->actingAs(admin())->put('/settings/mail', validSettings())->assertRedirect();

    $raw = DB::table('mail_settings')->value('password');

    expect($raw)->not->toBe('secret-app-password')
        ->and($raw)->not->toContain('secret');
});

test('the password is never sent to the browser', function () {
    MailSetting::store(validSettings());

    $this->actingAs(admin())
        ->get('/settings/mail')
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['hasPassword'])->toBeTrue()
                ->and($props['settings'])->not->toHaveKey('password')
                ->and(json_encode($props))->not->toContain('secret-app-password');
        });
});

test('saving with a blank password keeps the stored one', function () {
    MailSetting::store(validSettings());

    $this->actingAs(admin())
        ->put('/settings/mail', validSettings(['password' => '', 'host' => 'smtp.new.example.com']))
        ->assertRedirect();

    $row = MailSetting::query()->sole();

    expect($row->host)->toBe('smtp.new.example.com')
        ->and($row->password)->toBe('secret-app-password');
});

test('an smtp mailer requires a host and port', function () {
    $this->actingAs(admin())
        ->put('/settings/mail', validSettings(['host' => '', 'port' => '']))
        ->assertSessionHasErrors(['host', 'port']);
});

test('a non-smtp mailer does not require a host', function () {
    $this->actingAs(admin())
        ->put('/settings/mail', validSettings(['mailer' => 'log', 'host' => '', 'port' => '']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('an unknown mailer is rejected', function () {
    $this->actingAs(admin())
        ->put('/settings/mail', validSettings(['mailer' => 'carrier-pigeon']))
        ->assertSessionHasErrors('mailer');
});

test('saved settings override the environment mail config', function () {
    config(['mail.default' => 'log', 'mail.from.address' => 'env@example.com']);

    MailSetting::store(validSettings());

    // The provider applies settings during boot, so re-run that step.
    app()->make(AppServiceProvider::class, ['app' => app()])->boot();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtp')
        ->and(config('mail.from.address'))->toBe('noreply@example.com');
});

test('ssl encryption maps to the smtps scheme', function () {
    $settings = MailSetting::store(validSettings(['encryption' => 'ssl', 'port' => 465]));

    expect($settings->toConfig()['mail.mailers.smtp.scheme'])->toBe('smtps');
});

test('an smtp row without a host is not treated as usable', function () {
    $settings = new MailSetting(['mailer' => 'smtp', 'host' => null, 'port' => null]);

    expect($settings->isUsable())->toBeFalse();
});

test('a test email reaches the transport and the timestamp is recorded', function () {
    // The `array` transport keeps sent messages in memory, which lets the raw
    // test message be inspected — Mail::fake() only records Mailable classes.
    MailSetting::store(validSettings(['mailer' => 'array', 'host' => '', 'port' => null]));

    $this->actingAs(admin())
        ->post('/settings/mail/test', ['email' => 'principal@example.com'])
        ->assertRedirect();

    $sent = Mail::mailer('array')->getSymfonyTransport()->messages();

    expect($sent)->toHaveCount(1)
        ->and($sent[0]->getOriginalMessage()->getTo()[0]->getAddress())
        ->toBe('principal@example.com')
        ->and(MailSetting::query()->sole()->last_tested_at)->not->toBeNull();
});

test('a test email cannot be sent before the settings are saved', function () {
    Mail::fake();

    $this->actingAs(admin())
        ->post('/settings/mail/test', ['email' => 'principal@example.com'])
        ->assertRedirect();

    expect(MailSetting::query()->count())->toBe(0);
    Mail::assertNothingSent();
});

test('the test address must be a valid email', function () {
    MailSetting::store(validSettings());

    $this->actingAs(admin())
        ->post('/settings/mail/test', ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

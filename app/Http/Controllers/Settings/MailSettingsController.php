<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MailSettingsRequest;
use App\Models\MailSetting;
use App\Support\MailOAuthProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class MailSettingsController extends Controller
{
    public function edit(): Response
    {
        $settings = MailSetting::active();

        return Inertia::render('settings/Mail', [
            'settings' => [
                'mailer' => $settings?->mailer ?? config('mail.default'),
                'auth_type' => $settings?->auth_type ?? 'password',
                'host' => $settings?->host ?? config('mail.mailers.smtp.host'),
                'port' => $settings?->port ?? config('mail.mailers.smtp.port'),
                'encryption' => $settings?->encryption ?? '',
                'username' => $settings?->username ?? config('mail.mailers.smtp.username'),
                'from_address' => $settings?->from_address ?? config('mail.from.address'),
                'from_name' => $settings?->from_name ?? config('mail.from.name'),
                'verify_peer' => $settings?->verify_peer ?? true,
                'oauth_client_id' => $settings?->oauth_client_id,
                'oauth_email' => $settings?->oauth_email,
                'oauth_tenant' => $settings?->oauth_tenant,
            ],
            // Secrets are never sent to the browser, only whether they are set.
            'hasPassword' => filled($settings?->password),
            'hasClientSecret' => filled($settings?->oauth_client_secret),
            'isConnected' => (bool) $settings?->isConnected(),
            'tokenExpiresAt' => $settings?->oauth_expires_at?->toIso8601String(),
            'providers' => MailOAuthProvider::options(),
            'redirectUri' => route('mail.oauth.callback'),
            'isSaved' => $settings !== null,
            'lastTestedAt' => $settings?->last_tested_at?->toIso8601String(),
            'mailers' => MailSetting::MAILERS,
        ]);
    }

    public function update(MailSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /*
         * A blank secret means "leave the saved one alone", so that editing the
         * host does not silently wipe the credentials.
         */
        foreach (['password', 'oauth_client_secret'] as $secret) {
            if (blank($validated[$secret] ?? null)) {
                unset($validated[$secret]);
            }
        }

        $existing = MailSetting::active();

        // Changing provider invalidates the consent granted to the old one.
        if ($existing && $existing->auth_type !== ($validated['auth_type'] ?? 'password')) {
            $existing->forceFill([
                'oauth_refresh_token' => null,
                'oauth_access_token' => null,
                'oauth_expires_at' => null,
            ])->save();
        }

        MailSetting::store($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Email settings saved.']);

        return back();
    }

    /**
     * Send a test message using the saved settings, reporting the transport's
     * own error when it fails — that message is what tells an administrator
     * whether the host, port or credentials are wrong.
     */
    public function test(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('manage-users'), 403);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $settings = MailSetting::active();

        if (! $settings || ! $settings->isUsable()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Save the email settings before sending a test.',
            ]);

            return back();
        }

        try {
            Mail::raw(
                'This is a test message from '.config('app.name')."\n\n"
                    ."If you received it, the school website's email settings are working.",
                fn ($message) => $message
                    ->to($validated['email'])
                    ->subject(config('app.name').' — email test'),
            );
        } catch (Throwable $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Test failed: '.$e->getMessage(),
            ]);

            return back();
        }

        $settings->forceFill(['last_tested_at' => now()])->save();
        MailSetting::flush();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Test email sent to '.$validated['email'].'.',
        ]);

        return back();
    }
}

<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Mail\MailOAuthTokens;
use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * The OAuth 2 consent round trip for Microsoft 365 and Google mailboxes.
 *
 * The administrator saves a client id and secret, is sent to the provider to
 * grant access, and comes back with a code that is exchanged for a refresh
 * token. Only the refresh token is durable; access tokens are fetched from it
 * as needed when mail is sent.
 */
class MailOAuthController extends Controller
{
    private const STATE_KEY = 'mail-oauth-state';

    public function redirect(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('manage-users'), 403);

        $settings = MailSetting::active();
        $provider = $settings?->oauthProvider();

        if (! $settings || ! $provider) {
            return $this->back('error', 'Choose a sign-in method and save the settings first.');
        }

        if (blank($settings->oauth_client_id) || blank($settings->oauth_client_secret)) {
            return $this->back('error', 'Add the application ID and secret, then save, before connecting.');
        }

        if (blank($settings->oauth_email)) {
            return $this->back('error', 'Set the mailbox address before connecting.');
        }

        // Guards against a forged callback being used to plant tokens.
        $state = Str::random(40);
        $request->session()->put(self::STATE_KEY, $state);

        $query = http_build_query([
            'client_id' => $settings->oauth_client_id,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri(),
            'scope' => $provider->scopes(),
            'state' => $state,
            'login_hint' => $settings->oauth_email,
            ...$provider->authorizeParameters(),
        ]);

        return redirect()->away($provider->authorizeUrl($settings->oauth_tenant).'?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('manage-users'), 403);

        $expected = $request->session()->pull(self::STATE_KEY);

        if (blank($expected) || ! hash_equals((string) $expected, (string) $request->query('state'))) {
            return $this->back('error', 'The provider’s response did not match this session. Please try again.');
        }

        if ($request->query('error')) {
            return $this->back(
                'error',
                'Authorisation was declined: '.$request->query('error_description', $request->query('error')),
            );
        }

        $settings = MailSetting::active();
        $code = (string) $request->query('code');

        if (! $settings || blank($code)) {
            return $this->back('error', 'No authorisation code was returned.');
        }

        try {
            app(MailOAuthTokens::class)->exchangeCode($settings, $code, $this->redirectUri());
        } catch (Throwable $e) {
            return $this->back('error', 'Could not complete the connection: '.$e->getMessage());
        }

        return $this->back('success', 'Mailbox connected. Send a test email to confirm it works.');
    }

    /** Forget the tokens without touching the rest of the configuration. */
    public function disconnect(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('manage-users'), 403);

        MailSetting::active()?->forceFill([
            'oauth_refresh_token' => null,
            'oauth_access_token' => null,
            'oauth_expires_at' => null,
        ])->save();

        return $this->back('success', 'Mailbox disconnected.');
    }

    /**
     * Must match the redirect URI registered with the provider exactly, so it
     * is derived from the named route rather than typed in twice.
     */
    private function redirectUri(): string
    {
        return route('mail.oauth.callback');
    }

    private function back(string $type, string $message): RedirectResponse
    {
        session()->flash('toast', ['type' => $type, 'message' => $message]);

        return redirect()->route('mail.edit');
    }
}

<?php

namespace App\Actions\Mail;

use App\Models\MailSetting;
use App\Support\MailOAuthProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Exchanges and refreshes the OAuth 2 tokens used to authenticate SMTP.
 *
 * Access tokens last about an hour, so one is fetched on demand and kept until
 * shortly before it expires. The refresh token is the durable credential and is
 * only replaced when the provider sends a new one.
 */
class MailOAuthTokens
{
    /**
     * Refresh a little early, so a token cannot expire between the check and
     * the SMTP handshake.
     */
    private const EXPIRY_MARGIN_SECONDS = 120;

    /**
     * Swap the authorization code from the consent screen for tokens, and store
     * them against the settings row.
     */
    public function exchangeCode(MailSetting $settings, string $code, string $redirectUri): void
    {
        $provider = $settings->oauthProvider();

        if (! $provider) {
            throw new RuntimeException('This mail configuration does not use OAuth.');
        }

        $payload = $this->post($provider, $settings, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        if (blank($payload['refresh_token'] ?? null)) {
            throw new RuntimeException(
                'The provider did not return a refresh token. Remove the app\'s existing '
                .'consent and connect again so it is issued.'
            );
        }

        $settings->forceFill([
            'oauth_refresh_token' => $payload['refresh_token'],
            'oauth_access_token' => $payload['access_token'] ?? null,
            'oauth_expires_at' => now()->addSeconds((int) ($payload['expires_in'] ?? 3600)),
        ])->save();
    }

    /**
     * A usable access token, refreshing it first when needed.
     */
    public function accessToken(MailSetting $settings): string
    {
        if ($settings->hasFreshAccessToken(self::EXPIRY_MARGIN_SECONDS)) {
            return (string) $settings->oauth_access_token;
        }

        return $this->refresh($settings);
    }

    public function refresh(MailSetting $settings): string
    {
        $provider = $settings->oauthProvider();

        if (! $provider) {
            throw new RuntimeException('This mail configuration does not use OAuth.');
        }

        if (blank($settings->oauth_refresh_token)) {
            throw new RuntimeException('Not connected yet — authorise the mailbox first.');
        }

        $payload = $this->post($provider, $settings, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $settings->oauth_refresh_token,
        ]);

        $token = $payload['access_token'] ?? null;

        if (blank($token)) {
            throw new RuntimeException('The provider returned no access token.');
        }

        $settings->forceFill([
            'oauth_access_token' => $token,
            'oauth_expires_at' => now()->addSeconds((int) ($payload['expires_in'] ?? 3600)),
            // Google keeps the original refresh token; Microsoft rotates it.
            'oauth_refresh_token' => $payload['refresh_token'] ?? $settings->oauth_refresh_token,
        ])->save();

        return $token;
    }

    /**
     * @param  array<string, string>  $grant
     * @return array<string, mixed>
     */
    private function post(MailOAuthProvider $provider, MailSetting $settings, array $grant): array
    {
        $response = Http::asForm()
            ->timeout(20)
            ->post($provider->tokenUrl($settings->oauth_tenant), [
                'client_id' => $settings->oauth_client_id,
                'client_secret' => $settings->oauth_client_secret,
                'scope' => $provider->scopes(),
                ...$grant,
            ]);

        if ($response->failed()) {
            // The provider's own description is what tells an administrator
            // whether the client secret, redirect URI or consent is at fault.
            throw new RuntimeException($this->errorFrom($response->json(), $response->status()));
        }

        return (array) $response->json();
    }

    private function errorFrom(mixed $body, int $status): string
    {
        if (is_array($body)) {
            $description = $body['error_description'] ?? $body['error'] ?? null;

            if (is_string($description) && $description !== '') {
                // Microsoft returns multi-line descriptions with trace ids.
                return trim(strtok($description, "\r\n") ?: $description);
            }
        }

        return "The provider rejected the request (HTTP {$status}).";
    }
}

<?php

namespace App\Providers;

use App\Actions\Mail\MailOAuthTokens;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Throwable;

/**
 * Teaches Laravel's SMTP mailer to authenticate with OAuth 2 ("modern
 * authentication"), which Microsoft 365 and Google now require.
 *
 * XOAUTH2 sends the mailbox address as the username and a short-lived access
 * token in place of the password. The token is fetched when the transport is
 * built rather than stored in config, so it is always current and never ends up
 * in a cached config file.
 */
class MailOAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('smtp', function (array $config) {
            $transport = new EsmtpTransport(
                $config['host'] ?? '127.0.0.1',
                (int) ($config['port'] ?? 587),
                ($config['scheme'] ?? null) === 'smtps',
            );

            $this->applyVerifyPeer($transport, $config);

            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;

            if (filled($config['oauth'] ?? null)) {
                // Only offer XOAUTH2: left to negotiate, Symfony would try
                // PLAIN and LOGIN first and the server would reject them.
                $transport->setAuthenticators([new XOAuth2Authenticator]);
                $password = $this->accessToken();
            }

            if (filled($username)) {
                $transport->setUsername($username);
            }

            if (filled($password)) {
                $transport->setPassword($password);
            }

            return $transport;
        });
    }

    /**
     * The current access token for the saved mailbox.
     *
     * A failure here must not be swallowed: sending with an empty password
     * would fail at the SMTP handshake with a far less useful message than the
     * provider's own.
     */
    private function accessToken(): string
    {
        $settings = MailSetting::active();

        if (! $settings || ! $settings->isConnected()) {
            return '';
        }

        return app(MailOAuthTokens::class)->accessToken($settings);
    }

    /**
     * Honour MAIL_VERIFY_PEER=false, which exists for dev machines where
     * antivirus intercepts TLS.
     *
     * @param  array<string, mixed>  $config
     */
    private function applyVerifyPeer(EsmtpTransport $transport, array $config): void
    {
        if (($config['verify_peer'] ?? true) !== false) {
            return;
        }

        try {
            $stream = $transport->getStream();

            if (method_exists($stream, 'setStreamOptions')) {
                $stream->setStreamOptions([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
            }
        } catch (Throwable) {
            // Not fatal — the default (verifying) behaviour still applies.
        }
    }
}

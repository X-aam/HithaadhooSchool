<?php

namespace App\Support;

/**
 * Endpoints and defaults for the OAuth 2 mail providers.
 *
 * Microsoft and Google both authenticate SMTP with XOAUTH2: the username is the
 * mailbox address and the password is a short-lived access token, obtained once
 * from a consent screen and refreshed from a long-lived refresh token.
 */
enum MailOAuthProvider: string
{
    case Microsoft = 'microsoft';
    case Google = 'google';

    public function label(): string
    {
        return match ($this) {
            self::Microsoft => 'Microsoft 365',
            self::Google => 'Google Workspace / Gmail',
        };
    }

    /** SMTP host and port the provider requires; the UI fills these in. */
    public function smtp(): array
    {
        return match ($this) {
            self::Microsoft => ['host' => 'smtp.office365.com', 'port' => 587, 'encryption' => 'tls'],
            self::Google => ['host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'],
        };
    }

    public function authorizeUrl(?string $tenant = null): string
    {
        return match ($this) {
            self::Microsoft => "https://login.microsoftonline.com/{$this->tenant($tenant)}/oauth2/v2.0/authorize",
            self::Google => 'https://accounts.google.com/o/oauth2/v2/auth',
        };
    }

    public function tokenUrl(?string $tenant = null): string
    {
        return match ($this) {
            self::Microsoft => "https://login.microsoftonline.com/{$this->tenant($tenant)}/oauth2/v2.0/token",
            self::Google => 'https://oauth2.googleapis.com/token',
        };
    }

    /**
     * Scopes requested at consent. `offline_access` / `access_type=offline` are
     * what make the provider return a refresh token — without one the
     * connection would break as soon as the first access token expired.
     */
    public function scopes(): string
    {
        return match ($this) {
            self::Microsoft => 'offline_access https://outlook.office.com/SMTP.Send',
            self::Google => 'https://mail.google.com/',
        };
    }

    /**
     * Extra parameters on the authorize request.
     *
     * @return array<string, string>
     */
    public function authorizeParameters(): array
    {
        return match ($this) {
            // Google only issues a refresh token when both are present, and
            // only re-issues one when consent is forced.
            self::Google => ['access_type' => 'offline', 'prompt' => 'consent'],
            self::Microsoft => ['prompt' => 'consent'],
        };
    }

    /** Where an administrator registers the application. */
    public function consoleUrl(): string
    {
        return match ($this) {
            self::Microsoft => 'https://entra.microsoft.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade',
            self::Google => 'https://console.cloud.google.com/apis/credentials',
        };
    }

    private function tenant(?string $tenant): string
    {
        return filled($tenant) ? $tenant : 'common';
    }

    /** @return list<array{value: string, label: string, console: string}> */
    public static function options(): array
    {
        return array_map(fn (self $p) => [
            'value' => $p->value,
            'label' => $p->label(),
            'console' => $p->consoleUrl(),
        ], self::cases());
    }
}

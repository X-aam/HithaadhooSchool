<?php

namespace App\Models;

use App\Support\MailOAuthProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * The outgoing mail server configuration, held in one row so it can be edited
 * from the admin panel. Absent or incomplete settings mean the app keeps using
 * whatever the MAIL_* environment variables provide.
 */
class MailSetting extends Model
{
    /**
     * Cache key used by an earlier version that cached the model itself. Still
     * cleared on save so no stale payload can be left behind on a deployed
     * site; nothing reads it any more.
     */
    private const LEGACY_CACHE_KEY = 'mail-settings';

    protected $fillable = [
        'mailer',
        'auth_type',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'verify_peer',
        'last_tested_at',
        'oauth_client_id',
        'oauth_client_secret',
        'oauth_email',
        'oauth_tenant',
    ];

    protected $casts = [
        'port' => 'integer',
        'verify_peer' => 'boolean',
        'password' => 'encrypted',
        'last_tested_at' => 'datetime',
        'oauth_client_secret' => 'encrypted',
        'oauth_refresh_token' => 'encrypted',
        'oauth_access_token' => 'encrypted',
        'oauth_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'oauth_client_secret',
        'oauth_refresh_token',
        'oauth_access_token',
    ];

    /** Mailers an administrator can pick between in the admin panel. */
    public const MAILERS = ['smtp', 'log', 'sendmail', 'array'];

    public const ENCRYPTIONS = ['tls', 'ssl', ''];

    /**
     * The active settings row, read fresh.
     *
     * This is deliberately not cached. Caching the model meant serialising an
     * Eloquent object into the cache store, and a payload written by one deploy
     * unserialises to __PHP_Incomplete_Class under the next — which then fails
     * this method's return type and 500s the page. Caching the derived config
     * instead would be worse still: it would put the decrypted SMTP password
     * in the cache table in plain text.
     *
     * The cost is one indexed single-row query per request.
     */
    public static function active(): ?self
    {
        return self::query()->orderBy('id')->first();
    }

    /** Store the single row, replacing whatever was there. */
    public static function store(array $attributes): self
    {
        $row = self::query()->orderBy('id')->first();

        if ($row) {
            $row->fill($attributes)->save();
        } else {
            $row = self::query()->create($attributes);
        }

        self::flush();

        return $row->refresh();
    }

    public static function flush(): void
    {
        Cache::forget(self::LEGACY_CACHE_KEY);
    }

    /** The OAuth provider in use, or null for classic SMTP authentication. */
    public function oauthProvider(): ?MailOAuthProvider
    {
        return MailOAuthProvider::tryFrom((string) $this->auth_type);
    }

    public function usesOAuth(): bool
    {
        return $this->oauthProvider() !== null;
    }

    /** Has consent been granted and a refresh token stored? */
    public function isConnected(): bool
    {
        return $this->usesOAuth() && filled($this->oauth_refresh_token);
    }

    /** Is the stored access token still good, with a margin to spare? */
    public function hasFreshAccessToken(int $marginSeconds = 0): bool
    {
        return filled($this->oauth_access_token)
            && $this->oauth_expires_at !== null
            && $this->oauth_expires_at->isAfter(now()->addSeconds($marginSeconds));
    }

    /**
     * Is there enough here to actually send mail? An SMTP mailer without a host
     * would fail at send time, so it is treated as unconfigured.
     */
    public function isUsable(): bool
    {
        if ($this->mailer !== 'smtp') {
            return in_array($this->mailer, self::MAILERS, true);
        }

        if (! filled($this->host) || ! filled($this->port)) {
            return false;
        }

        // An OAuth mailbox cannot send until consent has actually been granted.
        return ! $this->usesOAuth() || $this->isConnected();
    }

    /**
     * The config overrides this row implies, ready to merge over config('mail').
     *
     * @return array<string, mixed>
     */
    public function toConfig(): array
    {
        $overrides = ['mail.default' => $this->mailer];

        if ($this->mailer === 'smtp') {
            $overrides += [
                'mail.mailers.smtp.host' => $this->host,
                'mail.mailers.smtp.port' => $this->port,
                /*
                 * With OAuth the mailbox address is the SMTP username and the
                 * access token stands in for the password (XOAUTH2). The token
                 * itself is resolved lazily by the transport, not here, so a
                 * stale one is never baked into the config.
                 */
                'mail.mailers.smtp.username' => $this->usesOAuth() ? $this->oauth_email : $this->username,
                'mail.mailers.smtp.password' => $this->usesOAuth() ? null : $this->password,
                'mail.mailers.smtp.oauth' => $this->usesOAuth() ? $this->auth_type : null,
                'mail.mailers.smtp.verify_peer' => $this->verify_peer,
                // Laravel reads an empty scheme as "decide from the port".
                'mail.mailers.smtp.scheme' => match ($this->encryption) {
                    'tls' => 'smtp',
                    'ssl' => 'smtps',
                    default => null,
                },
            ];
        }

        if (filled($this->from_address)) {
            $overrides['mail.from.address'] = $this->from_address;
        }

        if (filled($this->from_name)) {
            $overrides['mail.from.name'] = $this->from_name;
        }

        return $overrides;
    }
}

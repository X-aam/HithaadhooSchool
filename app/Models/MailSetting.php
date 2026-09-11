<?php

namespace App\Models;

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
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'verify_peer',
        'last_tested_at',
    ];

    protected $casts = [
        'port' => 'integer',
        'verify_peer' => 'boolean',
        'password' => 'encrypted',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['password'];

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

    /**
     * Is there enough here to actually send mail? An SMTP mailer without a host
     * would fail at send time, so it is treated as unconfigured.
     */
    public function isUsable(): bool
    {
        if ($this->mailer !== 'smtp') {
            return in_array($this->mailer, self::MAILERS, true);
        }

        return filled($this->host) && filled($this->port);
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
                'mail.mailers.smtp.username' => $this->username,
                'mail.mailers.smtp.password' => $this->password,
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

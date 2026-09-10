<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $fillable = ['key', 'value', 'draft'];

    protected $casts = [
        'value' => 'array',
        'draft' => 'array',
    ];

    /**
     * Get the published value for a content key, or the given default when the
     * section has never been published in the admin panel. Public pages use this.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->first()?->value ?? $default;
    }

    /**
     * The value the admin should edit: the latest unpublished draft when one
     * exists, otherwise the currently published value, otherwise the default.
     */
    public static function editable(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->draft ?? $row?->value ?? $default;
    }

    /**
     * Store a private draft without affecting the live/published value.
     */
    public static function saveDraft(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['draft' => $value]);
    }

    /**
     * Promote the pending draft to the live/published value.
     */
    public static function publish(string $key): void
    {
        $row = static::query()->where('key', $key)->first();

        if ($row && $row->draft !== null) {
            $row->value = $row->draft;
            $row->draft = null;
            $row->save();
        }
    }

    /**
     * Throw away the pending draft, keeping the published value intact.
     */
    public static function discardDraft(string $key): void
    {
        $row = static::query()->where('key', $key)->first();

        if ($row) {
            $row->draft = null;
            $row->save();
        }
    }

    /**
     * Create or update the published value directly (used by seeders).
     */
    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

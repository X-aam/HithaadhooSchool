<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds the Open Graph / link-preview metadata rendered into the page head.
 *
 * The site is client-rendered through Inertia, and link crawlers — Viber,
 * WhatsApp, Facebook, Slack — do not run JavaScript. They only ever see the
 * server-rendered blade head, so anything that should appear in a shared link's
 * preview has to be put there by the controller.
 *
 * A shared URL carries no locale, so previews show both languages: Dhivehi
 * first, then English.
 */
class PageMeta
{
    /** Fallback image when a page has nothing more specific to show. */
    public const DEFAULT_IMAGE = '/images/logo.png';

    /** Separators between the two languages. */
    private const TITLE_SEPARATOR = ' · ';

    private const TEXT_SEPARATOR = ' — ';

    /**
     * Each language gets its own budget, so a long Dhivehi excerpt cannot crowd
     * the English one out of the preview entirely.
     */
    private const PER_LANGUAGE_LIMIT = 140;

    /**
     * @param  mixed  $title  bilingual array or plain string, without site name
     * @param  mixed  $description  bilingual array or string; HTML is stripped
     * @param  string|null  $image  absolute URL or root-relative path
     * @param  string  $type  Open Graph type: 'website' or 'article'
     * @return array<string, string>
     */
    public static function make(
        mixed $title = null,
        mixed $description = null,
        ?string $image = null,
        string $type = 'website',
    ): array {
        $siteName = (string) config('app.name');
        $headline = self::bilingual($title, self::TITLE_SEPARATOR);

        return [
            // og:title omits the site name — og:site_name already carries it,
            // and chat clients truncate titles at around 35 characters.
            'title' => $headline ?? $siteName,
            'documentTitle' => filled($headline) ? $headline.' — '.$siteName : $siteName,
            'description' => self::bilingual($description, self::TEXT_SEPARATOR) ?? '',
            'image' => self::absoluteUrl($image ?: self::DEFAULT_IMAGE),
            'type' => $type,
        ];
    }

    /**
     * Join the Dhivehi and English sides of a bilingual field, keeping whichever
     * sides actually have content. Dhivehi leads: it is the school's primary
     * language, and it is the half a chat client shows before truncating.
     */
    public static function bilingual(mixed $value, string $separator = self::TEXT_SEPARATOR): ?string
    {
        if (! is_array($value)) {
            $single = self::text(is_string($value) ? $value : null);

            return $single !== '' ? $single : null;
        }

        $parts = [];

        foreach (['dv', 'en'] as $locale) {
            $text = self::text(is_string($value[$locale] ?? null) ? $value[$locale] : null);

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return $parts === [] ? null : implode($separator, $parts);
    }

    /**
     * Flatten rich text to a single plain-text line short enough for a preview.
     */
    private static function text(?string $value): string
    {
        $plain = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5));
        $plain = (string) preg_replace('/\s+/u', ' ', $plain);

        return Str::limit($plain, self::PER_LANGUAGE_LIMIT);
    }

    /**
     * Crawlers require an absolute og:image. Uploads are stored root-relative
     * on purpose, so they are resolved against APP_URL here — the one place an
     * absolute URL is actually wanted.
     */
    private static function absoluteUrl(string $path): string
    {
        if (str_contains($path, '://')) {
            return $path;
        }

        return url($path);
    }
}

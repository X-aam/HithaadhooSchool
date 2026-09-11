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
 */
class PageMeta
{
    /** Fallback image when a page has nothing more specific to show. */
    public const DEFAULT_IMAGE = '/images/logo.png';

    /**
     * @param  string|null  $title  page title, without the site name
     * @param  string|null  $description  plain text or HTML; tags are stripped
     * @param  string|null  $image  absolute URL or root-relative path
     * @param  string  $type  Open Graph type: 'website' or 'article'
     * @return array<string, string>
     */
    public static function make(
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
        string $type = 'website',
    ): array {
        $siteName = (string) config('app.name');

        return [
            'title' => filled($title) ? $title.' — '.$siteName : $siteName,
            'description' => self::text($description),
            'image' => self::absoluteUrl($image ?: self::DEFAULT_IMAGE),
            'type' => $type,
        ];
    }

    /**
     * Pick the English side of a bilingual field. Previews have no locale to
     * work from, and Thaana renders unreliably in chat clients.
     *
     * @param  mixed  $value  bilingual array or plain string
     */
    public static function fromBilingual(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach (['en', 'dv'] as $locale) {
                if (is_string($value[$locale] ?? null) && trim($value[$locale]) !== '') {
                    return $value[$locale];
                }
            }

            return null;
        }

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * Flatten rich text to a single plain-text line short enough for a preview.
     */
    private static function text(?string $value): string
    {
        $plain = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5));
        $plain = (string) preg_replace('/\s+/u', ' ', $plain);

        return Str::limit($plain, 200);
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

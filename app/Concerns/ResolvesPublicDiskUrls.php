<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait ResolvesPublicDiskUrls
{
    /**
     * Reduce a public-disk URL to a root-relative path, keeping any query
     * string.
     *
     * Links to uploaded files get pasted into site content — hero slides,
     * articles, the Downloads page — so an absolute URL would pin that content
     * to whichever host it happened to be authored on. Absolute URLs pointing
     * at another host (a CDN or S3-backed disk) are left alone, since they
     * cannot be expressed relative to this site.
     */
    protected function relativeUrl(string $url): string
    {
        if (! str_contains($url, '://')) {
            return Str::start($url, '/');
        }

        if (parse_url($url, PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST)) {
            return $url;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        return Str::start($path, '/').($query !== null ? '?'.$query : '');
    }
}

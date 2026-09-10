<?php

namespace App\Actions\Files;

use App\Http\Controllers\Admin\SiteContentController;
use App\Models\Announcement;
use App\Models\NewsArticle;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Find everywhere a file on the public disk is referenced, so an editor can see
 * what a file is used for before replacing or deleting it.
 */
class LocateFileUsage
{
    /**
     * Matching is done on the "storage/<path>" fragment rather than a full URL.
     * Links stored before uploads switched to relative paths still carry a
     * scheme and host, and both forms contain this fragment.
     *
     * @param  string  $path  path relative to the public disk root
     * @return list<array{label: string, context: string, editUrl: string|null}>
     */
    public function forPath(string $path): array
    {
        $needle = 'storage/'.ltrim($path, '/');

        return [
            ...$this->inSiteContent($needle),
            ...$this->inNews($needle),
            ...$this->inAnnouncements($needle),
            ...$this->inAvatars($needle),
        ];
    }

    /**
     * @return list<array{label: string, context: string, editUrl: string|null}>
     */
    private function inSiteContent(string $needle): array
    {
        $canEdit = Gate::allows('manage-content');
        $usages = [];

        foreach (SiteContent::query()->get() as $row) {
            $label = SiteContentController::SECTIONS[$row->key]['label'] ?? $row->key;
            $editUrl = $canEdit ? "/admin/content/{$row->key}/edit" : null;

            // Published and draft copies are reported separately: a file can be
            // live on the site, staged in an unpublished edit, or both.
            if ($this->contains($row->value, $needle)) {
                $usages[] = ['label' => $label, 'context' => 'Live on the site', 'editUrl' => $editUrl];
            }

            if ($this->contains($row->draft, $needle)) {
                $usages[] = ['label' => $label, 'context' => 'Unpublished draft', 'editUrl' => $editUrl];
            }
        }

        return $usages;
    }

    /**
     * @return list<array{label: string, context: string, editUrl: string|null}>
     */
    private function inNews(string $needle): array
    {
        $canEdit = Gate::allows('manage-news');
        $usages = [];

        foreach (NewsArticle::query()->get() as $article) {
            $inImage = is_string($article->image) && str_contains($article->image, $needle);
            $inBody = $this->contains($article->body, $needle) || $this->contains($article->excerpt, $needle);

            if (! $inImage && ! $inBody) {
                continue;
            }

            $usages[] = [
                'label' => $this->titleOf($article->title, $article->slug),
                'context' => 'News article — '.($inImage ? 'featured image' : 'in the body'),
                'editUrl' => $canEdit ? "/admin/news/{$article->id}/edit" : null,
            ];
        }

        return $usages;
    }

    /**
     * @return list<array{label: string, context: string, editUrl: string|null}>
     */
    private function inAnnouncements(string $needle): array
    {
        $canEdit = Gate::allows('manage-content');
        $usages = [];

        foreach (Announcement::query()->get() as $announcement) {
            if (! $this->contains($announcement->body, $needle)) {
                continue;
            }

            $usages[] = [
                'label' => $this->titleOf($announcement->title, 'Announcement #'.$announcement->id),
                'context' => 'Announcement — in the body',
                'editUrl' => $canEdit ? "/admin/announcements/{$announcement->id}/edit" : null,
            ];
        }

        return $usages;
    }

    /**
     * @return list<array{label: string, context: string, editUrl: string|null}>
     */
    private function inAvatars(string $needle): array
    {
        $canEdit = Gate::allows('manage-users');

        return User::query()
            ->whereNotNull('avatar')
            ->where('avatar', 'like', '%'.$needle.'%')
            ->get()
            ->map(fn (User $user) => [
                'label' => $user->name,
                'context' => 'Profile photo',
                'editUrl' => $canEdit ? "/admin/users/{$user->id}/edit" : null,
            ])
            ->all();
    }

    /**
     * Does a JSON-castable content value mention the file anywhere inside it?
     * Slashes are left unescaped so the needle matches the stored text.
     */
    private function contains(mixed $value, string $needle): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return str_contains($value, $needle);
        }

        $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return is_string($encoded) && str_contains($encoded, $needle);
    }

    /**
     * Pick a readable title out of a bilingual title field.
     */
    private function titleOf(mixed $title, string $fallback): string
    {
        if (is_array($title)) {
            foreach (['en', 'dv'] as $locale) {
                if (is_string($title[$locale] ?? null) && trim($title[$locale]) !== '') {
                    return $title[$locale];
                }
            }
        }

        if (is_string($title) && trim($title) !== '') {
            return $title;
        }

        return $fallback;
    }
}

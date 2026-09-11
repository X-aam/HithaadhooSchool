<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = [
        'slug',
        'category',
        'pinned',
        'is_published',
        'published_at',
        'title',
        'body',
        'attachments',
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'date',
        'title' => 'array',
        'body' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Announcements are addressed by slug, like news articles. One is derived
     * from the English title when the editor does not supply it.
     */
    protected static function booted(): void
    {
        static::saving(function (self $announcement): void {
            if (blank($announcement->slug)) {
                $announcement->slug = $announcement->uniqueSlug(
                    Str::slug($announcement->title['en'] ?? '') ?: 'announcement'
                );
            }
        });
    }

    /** Append a counter until the slug is free, ignoring this row. */
    public function uniqueSlug(string $base): string
    {
        $slug = $base;

        for ($i = 2; $this->slugTaken($slug); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    private function slugTaken(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }

    /**
     * Files attached to the announcement, normalised so the pages can rely on
     * every entry having the same keys.
     *
     * @return list<array{name: string, url: string, size: string, extension: string}>
     */
    public function attachmentList(): array
    {
        return collect($this->attachments ?? [])
            ->filter(fn ($file) => is_array($file) && filled($file['url'] ?? null))
            ->map(fn (array $file) => [
                'name' => (string) ($file['name'] ?? basename((string) $file['url'])),
                'url' => (string) $file['url'],
                'size' => (string) ($file['size'] ?? ''),
                'extension' => strtolower((string) ($file['extension'] ?? pathinfo((string) $file['url'], PATHINFO_EXTENSION))),
            ])
            ->values()
            ->all();
    }

    /** Shape a row for the public bilingual pages. */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category,
            'pinned' => (bool) $this->pinned,
            'date' => $this->published_at?->format('Y-m-d'),
            'title' => $this->title,
            'body' => $this->body,
            'attachments' => $this->attachmentList(),
        ];
    }
}

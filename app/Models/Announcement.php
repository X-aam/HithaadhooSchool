<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'category',
        'pinned',
        'is_published',
        'published_at',
        'title',
        'body',
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'date',
        'title' => 'array',
        'body' => 'array',
    ];

    /** Shape a row for the public bilingual pages. */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'pinned' => (bool) $this->pinned,
            'date' => $this->published_at?->format('Y-m-d'),
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}

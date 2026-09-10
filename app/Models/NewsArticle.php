<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'slug',
        'category',
        'image',
        'is_published',
        'published_at',
        'author',
        'title',
        'excerpt',
        'body',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'date',
        'author' => 'array',
        'title' => 'array',
        'excerpt' => 'array',
        'body' => 'array',
    ];

    /** Shape a row for the public bilingual pages. */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category,
            'date' => $this->published_at?->format('Y-m-d'),
            'image' => $this->image,
            'author' => $this->author,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
        ];
    }
}

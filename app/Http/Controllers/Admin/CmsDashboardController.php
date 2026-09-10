<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\NewsArticle;
use Inertia\Inertia;
use Inertia\Response;

class CmsDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'news' => NewsArticle::count(),
                'newsPublished' => NewsArticle::where('is_published', true)->count(),
                'announcements' => Announcement::count(),
                'announcementsPublished' => Announcement::where('is_published', true)->count(),
            ],
            'recentNews' => NewsArticle::query()
                ->orderByDesc('published_at')
                ->limit(5)
                ->get()
                ->map(fn (NewsArticle $a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'published_at' => $a->published_at?->format('Y-m-d'),
                    'is_published' => $a->is_published,
                ]),
            'recentAnnouncements' => Announcement::query()
                ->orderByDesc('published_at')
                ->limit(5)
                ->get()
                ->map(fn (Announcement $a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'published_at' => $a->published_at?->format('Y-m-d'),
                    'is_published' => $a->is_published,
                    'pinned' => $a->pinned,
                ]),
        ]);
    }
}

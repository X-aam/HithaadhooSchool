<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\NewsArticle;
use App\Models\SiteContent;
use App\Support\PageMeta;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('public/Home', [
            'hero' => SiteContent::get('hero'),
            'school' => SiteContent::get('school'),
            'featuredNews' => NewsArticle::query()
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map->toPublicArray(),
            'latestAnnouncements' => Announcement::query()
                ->where('is_published', true)
                ->orderByDesc('pinned')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map->toPublicArray(),
        ]);
    }

    public function newsIndex(): Response
    {
        return Inertia::render('public/News', [
            'articles' => NewsArticle::query()
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->get()
                ->map->toPublicArray(),
        ]);
    }

    public function newsShow(string $slug): Response
    {
        $articles = NewsArticle::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->get();

        $article = $articles->firstWhere('slug', $slug);

        return Inertia::render('public/NewsArticle', [
            'slug' => $slug,
            'articles' => $articles->map->toPublicArray(),
            // Shared links should preview the article itself, not the site.
            'meta' => $article
                ? PageMeta::make(
                    title: $article->title,
                    description: PageMeta::bilingual($article->excerpt)
                        ?? PageMeta::bilingual($article->body),
                    image: $article->image,
                    type: 'article',
                )
                : PageMeta::make(title: 'News'),
        ]);
    }

    public function announcementShow(string $slug): Response
    {
        $announcement = Announcement::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('public/Announcement', [
            'announcement' => $announcement->toPublicArray(),
            'meta' => PageMeta::make(
                title: $announcement->title,
                description: PageMeta::bilingual($announcement->body),
                type: 'article',
            ),
        ]);
    }

    public function announcements(): Response
    {
        return Inertia::render('public/Announcements', [
            'items' => Announcement::query()
                ->where('is_published', true)
                ->orderByDesc('pinned')
                ->orderByDesc('published_at')
                ->get()
                ->map->toPublicArray(),
        ]);
    }

    public function academicCalendar(): Response
    {
        return Inertia::render('public/AcademicCalendar', [
            'events' => SiteContent::get('academic_events'),
        ]);
    }

    public function activities(): Response
    {
        return Inertia::render('public/Activities', [
            'events' => SiteContent::get('activities'),
        ]);
    }

    public function timetable(): Response
    {
        return Inertia::render('public/Timetable', [
            'timetable' => SiteContent::get('timetable'),
        ]);
    }

    public function downloads(): Response
    {
        return Inertia::render('public/Downloads', [
            'items' => SiteContent::get('downloads'),
        ]);
    }

    public function team(): Response
    {
        return Inertia::render('public/Team', [
            'orgChart' => SiteContent::get('staff'),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('public/Contact', [
            'school' => SiteContent::get('school'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\NewsArticle;
use App\Models\SiteContent;
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
        return Inertia::render('public/NewsArticle', [
            'slug' => $slug,
            'articles' => NewsArticle::query()
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->get()
                ->map->toPublicArray(),
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

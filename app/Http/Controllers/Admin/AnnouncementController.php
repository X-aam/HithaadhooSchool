<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/announcements/Index', [
            'announcements' => Announcement::query()
                ->orderByDesc('pinned')
                ->orderByDesc('published_at')
                ->get()
                ->map(fn (Announcement $a) => [
                    'id' => $a->id,
                    'category' => $a->category,
                    'pinned' => $a->pinned,
                    'is_published' => $a->is_published,
                    'published_at' => $a->published_at?->format('Y-m-d'),
                    'title' => $a->title,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/announcements/Form', [
            'announcement' => null,
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        Announcement::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Announcement created.']);

        return redirect()->route('admin.announcements.index');
    }

    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('admin/announcements/Form', [
            'announcement' => [
                'id' => $announcement->id,
                'category' => $announcement->category,
                'pinned' => $announcement->pinned,
                'is_published' => $announcement->is_published,
                'published_at' => $announcement->published_at?->format('Y-m-d'),
                'title' => $announcement->title,
                'body' => $announcement->body,
            ],
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Announcement updated.']);

        return redirect()->route('admin.announcements.index');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Announcement deleted.']);

        return redirect()->route('admin.announcements.index');
    }
}

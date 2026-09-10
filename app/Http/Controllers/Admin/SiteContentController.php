<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteContentController extends Controller
{
    /**
     * Editable content sections. Each maps to a dedicated admin editor page.
     * The stored value overrides the site's built-in defaults; when a section
     * has never been saved the public pages fall back to the bundled content.
     */
    public const SECTIONS = [
        'school' => ['label' => 'School information', 'component' => 'admin/content/School'],
        'navigation' => ['label' => 'Navigation menu', 'component' => 'admin/content/Navigation'],
        'hero' => ['label' => 'Homepage hero slides', 'component' => 'admin/content/Hero'],
        'academic_events' => ['label' => 'Academic calendar', 'component' => 'admin/content/AcademicEvents'],
        'activities' => ['label' => 'Activities', 'component' => 'admin/content/Activities'],
        'timetable' => ['label' => 'Class timetable', 'component' => 'admin/content/Timetable'],
        'staff' => ['label' => 'Staff & organisation', 'component' => 'admin/content/Staff'],
        'downloads' => ['label' => 'Downloads', 'component' => 'admin/content/Downloads'],
    ];

    public function index(): Response
    {
        $rows = SiteContent::query()->get()->keyBy('key');

        return Inertia::render('admin/content/Index', [
            'sections' => collect(self::SECTIONS)
                ->map(function (array $config, string $key) use ($rows) {
                    $row = $rows->get($key);

                    return [
                        'key' => $key,
                        'label' => $config['label'],
                        'published' => ($row?->value) !== null,
                        'hasDraft' => ($row?->draft) !== null,
                    ];
                })
                ->values(),
        ]);
    }

    public function edit(string $section): Response
    {
        $config = self::SECTIONS[$section] ?? abort(404);

        $row = SiteContent::query()->where('key', $section)->first();

        return Inertia::render($config['component'], [
            'section' => $section,
            'label' => $config['label'],
            'value' => $row?->draft ?? $row?->value,
            'published' => ($row?->value) !== null,
            'hasDraft' => ($row?->draft) !== null,
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $config = self::SECTIONS[$section] ?? abort(404);

        $validated = $request->validate([
            'value' => ['required', 'array'],
        ]);

        SiteContent::saveDraft($section, $validated['value']);

        Inertia::flash('toast', ['type' => 'success', 'message' => $config['label'].' draft saved.']);

        return back();
    }

    public function publish(string $section): RedirectResponse
    {
        $config = self::SECTIONS[$section] ?? abort(404);

        SiteContent::publish($section);

        Inertia::flash('toast', ['type' => 'success', 'message' => $config['label'].' published to the live site.']);

        return back();
    }

    public function discard(string $section): RedirectResponse
    {
        $config = self::SECTIONS[$section] ?? abort(404);

        SiteContent::discardDraft($section);

        Inertia::flash('toast', ['type' => 'success', 'message' => $config['label'].' draft discarded.']);

        return back();
    }

    public function reset(string $section): RedirectResponse
    {
        $config = self::SECTIONS[$section] ?? abort(404);

        SiteContent::query()->where('key', $section)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => $config['label'].' reset to the default content.']);

        return back();
    }
}

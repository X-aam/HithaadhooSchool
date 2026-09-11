<?php

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function makeAnnouncement(array $overrides = []): Announcement
{
    return Announcement::query()->create(array_merge([
        'category' => 'general',
        'pinned' => false,
        'is_published' => true,
        'published_at' => '2026-06-20',
        'title' => ['en' => 'Term 3 Exam Timetable', 'dv' => 'ޓާމް 3'],
        'body' => ['en' => '<p>The timetable is <strong>now available</strong>.</p>', 'dv' => ''],
    ], $overrides));
}

test('an announcement gets a slug from its english title', function () {
    expect(makeAnnouncement()->slug)->toBe('term-3-exam-timetable');
});

test('announcements sharing a title get distinct slugs', function () {
    makeAnnouncement();
    $second = makeAnnouncement();

    expect($second->slug)->toBe('term-3-exam-timetable-2');
});

test('an announcement has its own page', function () {
    makeAnnouncement();

    $this->get('/announcements/term-3-exam-timetable')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/Announcement')
            ->where('announcement.title.en', 'Term 3 Exam Timetable'));
});

test('an unpublished announcement has no public page', function () {
    makeAnnouncement(['is_published' => false]);

    $this->get('/announcements/term-3-exam-timetable')->assertNotFound();
});

test('the rich text body is preserved, not flattened', function () {
    makeAnnouncement();

    $this->get('/announcements/term-3-exam-timetable')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('announcement.body.en', '<p>The timetable is <strong>now available</strong>.</p>'));
});

test('attachments are exposed on the announcement page', function () {
    makeAnnouncement([
        'attachments' => [
            ['name' => 'Exam Timetable.pdf', 'url' => '/storage/files/exam-timetable.pdf', 'size' => '240 KB', 'extension' => 'pdf'],
        ],
    ]);

    $this->get('/announcements/term-3-exam-timetable')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $files = $page->toArray()['props']['announcement']['attachments'];

            expect($files)->toHaveCount(1)
                ->and($files[0]['name'])->toBe('Exam Timetable.pdf')
                ->and($files[0]['url'])->toBe('/storage/files/exam-timetable.pdf');
        });
});

test('attachment entries without a url are dropped', function () {
    $announcement = makeAnnouncement([
        'attachments' => [
            ['name' => 'Good.pdf', 'url' => '/storage/files/good.pdf'],
            ['name' => 'Broken.pdf'],
        ],
    ]);

    $files = $announcement->attachmentList();

    expect($files)->toHaveCount(1)
        ->and($files[0]['name'])->toBe('Good.pdf')
        // Missing details are filled in rather than left undefined.
        ->and($files[0]['extension'])->toBe('pdf')
        ->and($files[0]['size'])->toBe('');
});

test('the listing carries slugs and attachments for every announcement', function () {
    makeAnnouncement([
        'attachments' => [['name' => 'Form.pdf', 'url' => '/storage/files/form.pdf']],
    ]);

    $this->get('/announcements')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $item = $page->toArray()['props']['items'][0];

            expect($item['slug'])->toBe('term-3-exam-timetable')
                ->and($item['attachments'])->toHaveCount(1);
        });
});

test('editors can save an announcement with attachments', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);

    $this->actingAs($editor)
        ->post('/admin/announcements', [
            'category' => 'academic',
            'pinned' => false,
            'is_published' => true,
            'published_at' => '2026-06-20',
            'title' => ['en' => 'Fee Structure 2026', 'dv' => ''],
            'body' => ['en' => '<p>See the attached circular.</p>', 'dv' => ''],
            'attachments' => [
                ['name' => 'Circular.pdf', 'url' => '/storage/files/circular.pdf', 'size' => '120 KB', 'extension' => 'pdf'],
            ],
        ])
        ->assertRedirect();

    $announcement = Announcement::query()->sole();

    expect($announcement->slug)->toBe('fee-structure-2026')
        ->and($announcement->attachments)->toHaveCount(1)
        ->and($announcement->attachments[0]['url'])->toBe('/storage/files/circular.pdf');
});

test('an attachment without a url is rejected', function () {
    $editor = User::factory()->create(['role' => UserRole::Editor]);

    $this->actingAs($editor)
        ->post('/admin/announcements', [
            'category' => 'general',
            'published_at' => '2026-06-20',
            'title' => ['en' => 'Broken', 'dv' => ''],
            'body' => ['en' => 'Body', 'dv' => ''],
            'attachments' => [['name' => 'No URL.pdf']],
        ])
        ->assertSessionHasErrors('attachments.0.url');
});

test('editing an announcement keeps its original slug', function () {
    $announcement = makeAnnouncement();
    $editor = User::factory()->create(['role' => UserRole::Editor]);

    $this->actingAs($editor)
        ->put("/admin/announcements/{$announcement->id}", [
            'category' => 'general',
            'pinned' => false,
            'is_published' => true,
            'published_at' => '2026-06-20',
            'title' => ['en' => 'A Completely New Title', 'dv' => ''],
            'body' => ['en' => 'Updated body', 'dv' => ''],
        ])
        ->assertRedirect();

    // Links already shared must keep working after an edit.
    expect($announcement->fresh()->slug)->toBe('term-3-exam-timetable');
});

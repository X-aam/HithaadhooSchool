<?php

use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\NewsArticle;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('uploads/slide.png', 'img');
});

function contentEditor(): User
{
    return User::factory()->create(['role' => UserRole::Editor]);
}

function usageFor(string $path = 'uploads/slide.png'): array
{
    return test()->actingAs(contentEditor())
        ->getJson('/admin/files/usage?path='.$path)
        ->assertOk()
        ->json('usages');
}

test('a file used by a published content section is reported as live', function () {
    SiteContent::query()->create([
        'key' => 'hero',
        'value' => [['image' => '/storage/uploads/slide.png', 'title' => ['en' => 'Welcome']]],
    ]);

    $usages = usageFor();

    expect($usages)->toHaveCount(1)
        ->and($usages[0]['label'])->toBe('Homepage hero slides')
        ->and($usages[0]['context'])->toBe('Live on the site')
        ->and($usages[0]['editUrl'])->toBe('/admin/content/hero/edit');
});

test('published and draft copies are reported separately', function () {
    SiteContent::query()->create([
        'key' => 'hero',
        'value' => [['image' => '/storage/uploads/slide.png']],
        'draft' => [['image' => '/storage/uploads/slide.png']],
    ]);

    expect(collect(usageFor())->pluck('context')->all())
        ->toBe(['Live on the site', 'Unpublished draft']);
});

test('legacy absolute urls left over from local uploads still match', function () {
    SiteContent::query()->create([
        'key' => 'hero',
        'value' => [['image' => 'http://localhost:8000/storage/uploads/slide.png']],
    ]);

    expect(usageFor())->toHaveCount(1);
});

test('a news featured image is reported with the article title', function () {
    NewsArticle::query()->create([
        'slug' => 'sports-day',
        'category' => 'events',
        'image' => '/storage/uploads/slide.png',
        'is_published' => true,
        'published_at' => now(),
        'author' => ['en' => 'Admin', 'dv' => 'އެޑްމިން'],
        'excerpt' => ['en' => 'A great day', 'dv' => 'ރަނގަޅު ދުވަސް'],
        'title' => ['en' => 'Sports Day', 'dv' => 'ކުޅިވަރު ދުވަސް'],
        'body' => ['en' => '<p>No images here.</p>'],
    ]);

    $usages = usageFor();

    expect($usages)->toHaveCount(1)
        ->and($usages[0]['label'])->toBe('Sports Day')
        ->and($usages[0]['context'])->toBe('News article — featured image');
});

test('an image embedded in an article body is found', function () {
    NewsArticle::query()->create([
        'slug' => 'sports-day',
        'category' => 'events',
        'is_published' => true,
        'published_at' => now(),
        'author' => ['en' => 'Admin', 'dv' => 'އެޑްމިން'],
        'excerpt' => ['en' => 'A great day'],
        'title' => ['en' => 'Sports Day'],
        'body' => ['en' => '<p>See <img src="/storage/uploads/slide.png"> here</p>'],
    ]);

    expect(collect(usageFor())->pluck('context')->all())->toBe(['News article — in the body']);
});

test('an image embedded in an announcement is found', function () {
    Announcement::query()->create([
        'category' => 'general',
        'is_published' => true,
        'published_at' => now(),
        'title' => ['en' => 'Term starts'],
        'body' => ['en' => '<img src="/storage/uploads/slide.png">'],
    ]);

    $usages = usageFor();

    expect($usages)->toHaveCount(1)
        ->and($usages[0]['label'])->toBe('Term starts')
        ->and($usages[0]['context'])->toBe('Announcement — in the body');
});

test('a profile photo is reported, without an edit link for non-admins', function () {
    User::factory()->create(['name' => 'Aishath Ali', 'avatar' => '/storage/uploads/slide.png']);

    $usages = usageFor();

    expect($usages)->toHaveCount(1)
        ->and($usages[0]['label'])->toBe('Aishath Ali')
        ->and($usages[0]['context'])->toBe('Profile photo')
        ->and($usages[0]['editUrl'])->toBeNull();
});

test('an unused file reports no usages', function () {
    SiteContent::query()->create([
        'key' => 'hero',
        'value' => [['image' => '/storage/uploads/other.png']],
    ]);

    expect(usageFor())->toBe([]);
});

test('a similarly named file is not mistaken for the one being checked', function () {
    Storage::disk('public')->put('uploads/slide-2.png', 'img');

    SiteContent::query()->create([
        'key' => 'hero',
        'value' => [['image' => '/storage/uploads/slide-2.png']],
    ]);

    expect(usageFor('uploads/slide.png'))->toBe([]);
    expect(usageFor('uploads/slide-2.png'))->toHaveCount(1);
});

test('usage lookup 404s for a file that does not exist', function () {
    $this->actingAs(contentEditor())
        ->getJson('/admin/files/usage?path=uploads/missing.png')
        ->assertNotFound();
});

test('usage lookup rejects path traversal', function () {
    $this->actingAs(contentEditor())
        ->getJson('/admin/files/usage?path=../../.env')
        ->assertNotFound();
});

test('authors cannot look up file usage', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Author]))
        ->getJson('/admin/files/usage?path=uploads/slide.png')
        ->assertForbidden();
});

<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Storage::fake('public');
});

function editor(): User
{
    return User::factory()->create(['role' => UserRole::Editor]);
}

test('editors can view the file manager', function () {
    $this->actingAs(editor())
        ->get('/admin/files')
        ->assertOk();
});

test('authors cannot access the file manager', function () {
    $author = User::factory()->create(['role' => UserRole::Author]);

    $this->actingAs($author)->get('/admin/files')->assertForbidden();
    $this->actingAs($author)
        ->post('/admin/files', ['file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])
        ->assertForbidden();
});

test('editors can upload a file', function () {
    $this->actingAs(editor())
        ->post('/admin/files', ['file' => UploadedFile::fake()->create('Admission Form 2026.pdf', 100, 'application/pdf')])
        ->assertRedirect();

    Storage::disk('public')->assertExists('files/admission-form-2026.pdf');
});

test('uploads with a duplicate name get a unique suffix', function () {
    Storage::disk('public')->put('files/form.pdf', 'existing');

    $this->actingAs(editor())
        ->post('/admin/files', ['file' => UploadedFile::fake()->create('form.pdf', 100, 'application/pdf')])
        ->assertRedirect();

    Storage::disk('public')->assertExists('files/form-1.pdf');
});

test('disallowed file types are rejected', function () {
    $this->actingAs(editor())
        ->post('/admin/files', ['file' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php')])
        ->assertSessionHasErrors('file');
});

test('editors can delete a file', function () {
    Storage::disk('public')->put('files/old.pdf', 'content');

    $this->actingAs(editor())
        ->delete('/admin/files/files/old.pdf')
        ->assertRedirect();

    Storage::disk('public')->assertMissing('files/old.pdf');
});

test('editors can delete an image uploaded through a page editor', function () {
    Storage::disk('public')->put('uploads/slide.png', 'content');

    $this->actingAs(editor())
        ->delete('/admin/files/uploads/slide.png')
        ->assertRedirect();

    Storage::disk('public')->assertMissing('uploads/slide.png');
});

test('deleting a file that does not exist is a 404', function () {
    $this->actingAs(editor())
        ->delete('/admin/files/uploads/nope.png')
        ->assertNotFound();
});

test('a directory cannot be deleted as if it were a file', function () {
    Storage::disk('public')->put('uploads/slide.png', 'content');

    $this->actingAs(editor())
        ->delete('/admin/files/uploads')
        ->assertNotFound();

    Storage::disk('public')->assertExists('uploads/slide.png');
});

test('path traversal in delete is rejected', function () {
    Storage::disk('public')->put('uploads/image.png', 'content');

    $this->actingAs(editor())
        ->delete('/admin/files/..%2F..%2Fuploads%2Fimage.png')
        ->assertNotFound();

    $this->actingAs(editor())
        ->delete('/admin/files/uploads%2F..%2Fuploads%2Fimage.png')
        ->assertNotFound();

    Storage::disk('public')->assertExists('uploads/image.png');
});

test('the root lists every upload directory under a friendly label', function () {
    $disk = Storage::disk('public');
    $disk->put('files/prospectus.pdf', 'doc');
    $disk->put('uploads/hero-slide.png', 'img');
    $disk->put('.gitignore', 'ignored');

    $this->actingAs(editor())
        ->get('/admin/files')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $props = $page->toArray()['props'];
            $directories = collect($props['directories']);

            expect($directories->pluck('path')->all())->toContain('files', 'uploads')
                ->and($directories->firstWhere('path', 'uploads')['label'])->toBe('Page editor uploads')
                ->and($directories->firstWhere('path', 'files')['label'])->toBe('Media library')
                ->and($directories->firstWhere('path', 'uploads')['count'])->toBe(1)
                ->and(collect($props['files'])->pluck('name')->all())->not->toContain('.gitignore');
        });
});

test('browsing a directory lists its files with breadcrumbs', function () {
    Storage::disk('public')->put('uploads/hero-slide.png', 'img');

    $this->actingAs(editor())
        ->get('/admin/files?path=uploads')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $props = $page->toArray()['props'];
            $file = collect($props['files'])->firstWhere('path', 'uploads/hero-slide.png');

            expect($props['path'])->toBe('uploads')
                ->and($file['name'])->toBe('hero-slide.png')
                ->and($file['extension'])->toBe('png')
                ->and(collect($props['breadcrumbs'])->pluck('name')->all())->toBe(['Files', 'Page editor uploads']);
        });
});

test('every listed file gets a root-relative url, whatever its type', function () {
    config(['app.url' => 'http://localhost:8000']);

    $disk = Storage::disk('public');
    $disk->put('files/prospectus.pdf', 'doc');
    $disk->put('files/roster.xlsx', 'sheet');
    $disk->put('files/logo.png', 'img');

    $this->actingAs(editor())
        ->get('/admin/files?path=files')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $urls = collect($page->toArray()['props']['files'])->pluck('url');

            expect($urls)->toHaveCount(3);

            foreach ($urls as $url) {
                expect($url)->toStartWith('/storage/files/')
                    ->and($url)->not->toContain('localhost');
            }
        });
});

test('listed files keep an absolute url when the disk points at another host', function () {
    config(['app.url' => 'http://localhost:8000']);
    Storage::fake('public', ['url' => 'https://cdn.example.com/storage']);
    Storage::disk('public')->put('files/prospectus.pdf', 'doc');

    $this->actingAs(editor())
        ->get('/admin/files?path=files')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $url = collect($page->toArray()['props']['files'])->firstWhere('name', 'prospectus.pdf')['url'];

            expect($url)->toBe('https://cdn.example.com/storage/files/prospectus.pdf');
        });
});

test('browsing an unknown directory is a 404', function () {
    $this->actingAs(editor())
        ->get('/admin/files?path=nope')
        ->assertNotFound();
});

test('path traversal in the browse path is rejected', function () {
    $this->actingAs(editor())
        ->get('/admin/files?path=../../config')
        ->assertNotFound();
});

test('uploads land in the folder being browsed', function () {
    Storage::disk('public')->put('uploads/keep.png', 'img');

    $this->actingAs(editor())
        ->post('/admin/files', [
            'file' => UploadedFile::fake()->image('Hero Slide.png'),
            'path' => 'uploads',
        ])
        ->assertRedirect();

    Storage::disk('public')->assertExists('uploads/hero-slide.png');
});

test('uploads from the root land in the documents folder', function () {
    $this->actingAs(editor())
        ->post('/admin/files', ['file' => UploadedFile::fake()->create('Notice.pdf', 10, 'application/pdf')])
        ->assertRedirect();

    Storage::disk('public')->assertExists('files/notice.pdf');
});

test('uploads cannot escape the disk with a crafted path', function () {
    $this->actingAs(editor())
        ->post('/admin/files', [
            'file' => UploadedFile::fake()->image('slide.png'),
            'path' => '../../config',
        ])
        ->assertNotFound();
});

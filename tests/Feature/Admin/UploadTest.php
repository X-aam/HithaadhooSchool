<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('uploads return a root-relative path rather than an absolute url', function () {
    config(['app.url' => 'http://localhost:8000']);

    $response = $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->postJson('/admin/uploads', ['image' => UploadedFile::fake()->image('slide.png')])
        ->assertOk();

    $url = $response->json('url');

    expect($url)->toStartWith('/storage/uploads/')
        ->and($url)->not->toContain('localhost');

    Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));
});

test('uploads keep an absolute url when the disk points at another host', function () {
    config(['app.url' => 'http://localhost:8000']);
    Storage::fake('public', ['url' => 'https://cdn.example.com/storage']);

    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->postJson('/admin/uploads', ['image' => UploadedFile::fake()->image('slide.png')])
        ->assertOk()
        ->assertJson(fn ($json) => $json->where('url', fn (string $url) => str_starts_with($url, 'https://cdn.example.com/storage/uploads/')));
});

test('guests cannot upload', function () {
    $this->postJson('/admin/uploads', ['image' => UploadedFile::fake()->image('slide.png')])
        ->assertUnauthorized();
});

test('documents upload with a readable name and describe themselves back', function () {
    config(['app.url' => 'http://localhost:8000']);

    $response = $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->postJson('/admin/uploads/file', [
            'file' => UploadedFile::fake()->create('Admission Form 2026.pdf', 240, 'application/pdf'),
        ])
        ->assertOk();

    expect($response->json('url'))->toBe('/storage/files/admission-form-2026.pdf')
        ->and($response->json('name'))->toBe('admission-form-2026')
        ->and($response->json('extension'))->toBe('pdf')
        ->and($response->json('size'))->toBe('240 KB');

    Storage::disk('public')->assertExists('files/admission-form-2026.pdf');
});

test('a document upload does not overwrite an existing name', function () {
    Storage::disk('public')->put('files/notice.pdf', 'first');

    $response = $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->postJson('/admin/uploads/file', [
            'file' => UploadedFile::fake()->create('notice.pdf', 10, 'application/pdf'),
        ])
        ->assertOk();

    expect($response->json('url'))->toBe('/storage/files/notice-1.pdf');
    expect(Storage::disk('public')->get('files/notice.pdf'))->toBe('first');
});

test('disallowed document types are rejected', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->postJson('/admin/uploads/file', [
            'file' => UploadedFile::fake()->create('evil.php', 10, 'application/x-php'),
        ])
        ->assertStatus(422);
});

test('authors cannot upload documents', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Author]))
        ->postJson('/admin/uploads/file', [
            'file' => UploadedFile::fake()->create('notice.pdf', 10, 'application/pdf'),
        ])
        ->assertForbidden();
});

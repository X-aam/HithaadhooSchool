<?php

use App\Enums\UserRole;
use App\Models\User;

test('the cms does not admit to existing for signed-out visitors', function () {
    $this->get('/admin')->assertNotFound();
    $this->get('/admin/news')->assertNotFound();
    $this->get('/admin/content')->assertNotFound();
});

test('settings are hidden from signed-out visitors too', function () {
    $this->get('/settings/profile')->assertNotFound();
    $this->get('/settings/mail')->assertNotFound();
});

test('no response leaks the obscure login path', function () {
    // The whole point: a redirect would hand out FORTIFY_PREFIX.
    foreach (['/admin', '/admin/users', '/settings/profile'] as $path) {
        $response = $this->get($path);

        expect($response->headers->get('Location'))->toBeNull()
            ->and($response->getStatusCode())->toBe(404);
    }
});

test('the login page itself stays reachable', function () {
    $this->get(route('login'))->assertOk();
});

test('signed-in staff reach the cms normally', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->get('/admin')
        ->assertOk();
});

test('public pages are unaffected', function () {
    $this->get('/')->assertOk();
    $this->get('/news')->assertOk();
    $this->get('/announcements')->assertOk();
});

test('a signed-in user without permission still gets 403, not 404', function () {
    // Hiding is only for guests; an authenticated user gets the real answer.
    $this->actingAs(User::factory()->create(['role' => UserRole::Author]))
        ->get('/admin/users')
        ->assertForbidden();
});

<?php

use App\Enums\UserRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('public pages expose the signed-in user, so the cms shortcut can show', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Editor]))
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('auth.user'));
});

test('public pages expose no user to visitors, so the shortcut stays hidden', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.user', null));
});

test('the shortcut target is hidden from guests', function () {
    // Nothing is revealed by the shortcut's absence: /admin 404s for guests.
    $this->get('/admin')->assertNotFound();
});

<?php

use App\Models\NewsArticle;

function publishedArticle(array $overrides = []): NewsArticle
{
    return NewsArticle::query()->create(array_merge([
        'slug' => 'grade-10-farewell-assembly',
        'category' => 'events',
        'image' => '/storage/uploads/farewell.jpg',
        'is_published' => true,
        'published_at' => now(),
        'author' => ['en' => 'Admin', 'dv' => 'އެޑްމިން'],
        'title' => ['en' => 'Grade 10 Farewell Assembly', 'dv' => 'ގްރޭޑް 10 ވަދާޢީ ޖަލްސާ'],
        'excerpt' => ['en' => 'A warm send-off for our graduating students.', 'dv' => 'ތަހުނިޔާ'],
        'body' => ['en' => '<p>The assembly was held in the school hall.</p>'],
    ], $overrides));
}

test('an article link preview carries its own title, excerpt and photo', function () {
    config(['app.name' => 'Hithaadhoo School']);

    publishedArticle();

    $html = $this->get('/news/grade-10-farewell-assembly')->assertOk()->getContent();

    expect($html)
        ->toContain('<meta property="og:title" content="Grade 10 Farewell Assembly — Hithaadhoo School">')
        ->toContain('<meta property="og:description" content="A warm send-off for our graduating students.">')
        ->toContain('<meta property="og:type" content="article">')
        // Crawlers require an absolute image URL, even though uploads are
        // stored root-relative.
        ->toContain('content="'.url('/storage/uploads/farewell.jpg').'"')
        ->and($html)->not->toContain('<title>Laravel</title>');
});

test('the page title is the article, not the framework name', function () {
    config(['app.name' => 'Hithaadhoo School']);

    publishedArticle();

    $this->get('/news/grade-10-farewell-assembly')
        ->assertOk()
        ->assertSee('<title>Grade 10 Farewell Assembly — Hithaadhoo School</title>', false);
});

test('article html is stripped out of the preview description', function () {
    publishedArticle(['excerpt' => ['en' => '<p>Tags <strong>removed</strong>.</p>']]);

    $this->get('/news/grade-10-farewell-assembly')
        ->assertOk()
        ->assertSee('content="Tags removed."', false);
});

test('an article without an excerpt falls back to its body', function () {
    publishedArticle(['excerpt' => ['en' => '']]);

    $this->get('/news/grade-10-farewell-assembly')
        ->assertOk()
        ->assertSee('content="The assembly was held in the school hall."', false);
});

test('an article without a photo falls back to the school logo', function () {
    publishedArticle(['image' => null]);

    $this->get('/news/grade-10-farewell-assembly')
        ->assertOk()
        ->assertSee('content="'.url('/images/logo.png').'"', false);
});

test('other public pages still get site-level preview tags', function () {
    config(['app.name' => 'Hithaadhoo School']);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<meta property="og:site_name" content="Hithaadhoo School">')
        ->toContain('<meta property="og:type" content="website">')
        ->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->and($html)->not->toContain('content="Laravel"');
});

test('the canonical url points at the page being viewed', function () {
    publishedArticle();

    $this->get('/news/grade-10-farewell-assembly')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.url('/news/grade-10-farewell-assembly').'">', false);
});

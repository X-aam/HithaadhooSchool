<?php

use App\Models\NewsArticle;

const TITLE_EN = 'Quran Recitation Competition';
const TITLE_DV = 'ޤުރުއާން ކިޔެވުމުގެ މުބާރާތް';
const EXCERPT_EN = 'Students showcased their talent in recitation and tajweed.';
const EXCERPT_DV = 'ދަރިވަރުން ކިޔެވުމުގައި ހުނަރު ދައްކާލި.';

function publishedArticle(array $overrides = []): NewsArticle
{
    return NewsArticle::query()->create(array_merge([
        'slug' => 'quran-recitation-competition',
        'category' => 'events',
        'image' => '/storage/uploads/recitation.jpg',
        'is_published' => true,
        'published_at' => now(),
        'author' => ['en' => 'Admin', 'dv' => 'އެޑްމިން'],
        'title' => ['en' => TITLE_EN, 'dv' => TITLE_DV],
        'excerpt' => ['en' => EXCERPT_EN, 'dv' => EXCERPT_DV],
        'body' => ['en' => '<p>The competition was held in the school hall.</p>'],
    ], $overrides));
}

function previewHtml(string $slug = 'quran-recitation-competition'): string
{
    return test()->get("/news/{$slug}")->assertOk()->getContent();
}

test('a preview carries both languages, Dhivehi first', function () {
    publishedArticle();

    expect(previewHtml())
        ->toContain('<meta property="og:title" content="'.TITLE_DV.' · '.TITLE_EN.'">')
        ->toContain('<meta property="og:description" content="'.EXCERPT_DV.' — '.EXCERPT_EN.'">');
});

test('og:title omits the site name so chat clients do not truncate it away', function () {
    config(['app.name' => 'Hithaadhoo School']);

    publishedArticle();

    $html = previewHtml();

    // The site name belongs in og:site_name and the document title, not og:title.
    expect($html)
        ->toContain('<meta property="og:title" content="'.TITLE_DV.' · '.TITLE_EN.'">')
        ->toContain('<meta property="og:site_name" content="Hithaadhoo School">')
        ->toContain('<title>'.TITLE_DV.' · '.TITLE_EN.' — Hithaadhoo School</title>');
});

test('both locales are declared', function () {
    publishedArticle();

    expect(previewHtml())
        ->toContain('<meta property="og:locale" content="dv_MV">')
        ->toContain('<meta property="og:locale:alternate" content="en_GB">');
});

test('an article with only one language previews with just that one', function () {
    publishedArticle([
        'title' => ['en' => TITLE_EN, 'dv' => ''],
        'excerpt' => ['en' => EXCERPT_EN, 'dv' => ''],
    ]);

    $html = previewHtml();

    // No dangling separator when a side is empty.
    expect($html)
        ->toContain('<meta property="og:title" content="'.TITLE_EN.'">')
        ->and($html)->not->toContain('content=" · '.TITLE_EN.'"');
});

test('the article photo is used, absolutised for crawlers', function () {
    publishedArticle();

    expect(previewHtml())->toContain('content="'.url('/storage/uploads/recitation.jpg').'"');
});

test('article html is stripped out of the preview description', function () {
    publishedArticle([
        'excerpt' => ['en' => '<p>Tags <strong>removed</strong>.</p>', 'dv' => ''],
    ]);

    expect(previewHtml())->toContain('content="Tags removed."');
});

test('an article without an excerpt falls back to its body', function () {
    publishedArticle(['excerpt' => ['en' => '', 'dv' => '']]);

    expect(previewHtml())->toContain('content="The competition was held in the school hall."');
});

test('an article without a photo falls back to the school logo', function () {
    publishedArticle(['image' => null]);

    expect(previewHtml())->toContain('content="'.url('/images/logo.png').'"');
});

test('each language is truncated on its own budget', function () {
    publishedArticle([
        'excerpt' => ['en' => str_repeat('a', 300), 'dv' => str_repeat('ހ', 300)],
    ]);

    $html = previewHtml();

    // Both halves survive: a long Dhivehi excerpt must not crowd out the English.
    expect($html)->toContain('aaa')
        ->and($html)->toContain('ހހހ')
        ->and($html)->toContain(' — ');
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

    expect(previewHtml())
        ->toContain('<link rel="canonical" href="'.url('/news/quran-recitation-competition').'">');
});

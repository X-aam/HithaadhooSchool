<?php

use App\Models\SiteContent;
use Inertia\Testing\AssertableInertia;

test('the timetable page renders with no content saved', function () {
    $this->get('/timetable')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('public/Timetable'));
});

test('the timetable page renders content in the per-class shape', function () {
    SiteContent::query()->create([
        'key' => 'timetable',
        'value' => [
            'grades' => [
                [
                    'name' => 'Grade 8',
                    'classes' => [
                        ['name' => 'A', 'days' => [['day' => ['en' => 'Sunday'], 'slots' => []]]],
                        ['name' => 'B', 'days' => [['day' => ['en' => 'Sunday'], 'slots' => []]]],
                    ],
                ],
            ],
        ],
    ]);

    $this->get('/timetable')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $classes = $page->toArray()['props']['timetable']['grades'][0]['classes'];

            expect(collect($classes)->pluck('name')->all())->toBe(['A', 'B']);
        });
});

test('the timetable page still renders content saved in the old flat shape', function () {
    // Before per-class timetables, "grades" was a list of names and every grade
    // shared one set of days. That content is normalised in the page component.
    SiteContent::query()->create([
        'key' => 'timetable',
        'value' => [
            'grades' => ['Grade 6', 'Grade 7'],
            'days' => [['day' => ['en' => 'Sunday', 'dv' => 'އާދިއްތަ'], 'slots' => []]],
        ],
    ]);

    $this->get('/timetable')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $timetable = $page->toArray()['props']['timetable'];

            expect($timetable['grades'])->toBe(['Grade 6', 'Grade 7'])
                ->and($timetable['days'])->toHaveCount(1);
        });
});

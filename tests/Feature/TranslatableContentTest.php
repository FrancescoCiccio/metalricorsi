<?php

use App\Models\Course;
use App\Models\Download;
use App\Models\Video;

test('un contenuto tradotto mostra la lingua corrente', function () {
    $course = Course::create([
        'title' => ['it' => 'Corso saldatura', 'en' => 'Welding course'],
        'description' => ['it' => 'Descrizione', 'en' => 'Description'],
        'when' => now()->addDay(),
    ]);

    app()->setLocale('en');

    expect($course->title)->toBe('Welding course');
});

test('un contenuto senza traduzione fa fallback sull italiano', function () {
    $course = Course::create([
        'title' => ['it' => 'Corso saldatura'],
        'when' => now()->addDay(),
    ]);

    app()->setLocale('en');

    expect($course->title)->toBe('Corso saldatura');
});

test('video e download sono traducibili con fallback', function () {
    $video = Video::create(['title' => ['it' => 'Video ita']]);
    $download = Download::create(['title' => ['it' => 'Dispensa'], 'file_path' => 'x.pdf']);

    app()->setLocale('fr');

    expect($video->title)->toBe('Video ita')
        ->and($download->title)->toBe('Dispensa');
});

test('la descrizione di un download è mass-assignable e traducibile', function () {
    $download = Download::create([
        'title' => ['it' => 'Dispensa'],
        'description' => ['it' => 'Contenuto', 'en' => 'Content'],
        'file_path' => 'x.pdf',
    ]);

    app()->setLocale('en');

    expect($download->fresh()->description)->toBe('Content');
});

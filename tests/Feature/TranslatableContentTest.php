<?php

use App\Models\Course;
use App\Models\Download;
use App\Models\Video;
use App\Models\User;
use Livewire\Livewire;

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

test('la ricerca corsi trova sia la lingua corrente sia l italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    Course::create(['title' => ['it' => 'Corso saldatura', 'en' => 'Welding course'], 'when' => now()->addDay()]);
    Course::create(['title' => ['it' => 'Corso bullonatura'], 'when' => now()->addDay()]);

    $this->actingAs($user);
    app()->setLocale('en');

    Livewire::test(\App\Livewire\Course\Index::class)
        ->set('search', 'Welding')
        ->assertSee('Welding course');

    Livewire::test(\App\Livewire\Course\Index::class)
        ->set('search', 'bullonatura')
        ->assertSee('Corso bullonatura');
});

test('la ricerca video trova sia la lingua corrente sia l italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    Video::create(['title' => ['it' => 'Video montaggio', 'en' => 'Assembly video']]);

    $this->actingAs($user);
    app()->setLocale('en');

    Livewire::test(\App\Livewire\Video\Index::class)
        ->set('search', 'Assembly')
        ->assertSee('Assembly video');

    Livewire::test(\App\Livewire\Video\Index::class)
        ->set('search', 'montaggio')
        ->assertSee('Assembly video');
});

test('la ricerca esclude titoli presenti solo in una lingua diversa da quella corrente o italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    $course = Course::create(['title' => ['fr' => 'Charpente metallique'], 'when' => now()->addDay()]);

    $this->actingAs($user);
    app()->setLocale('en');

    // Non si può verificare l'esclusione tramite assertDontSee('Charpente metallique'):
    // l'accessor traducibile restituisce stringa vuota per una lingua non tradotta e
    // priva di fallback, quindi il titolo non comparirebbe mai in pagina a prescindere
    // dalla query usata. Si verifica invece direttamente il risultato della query.
    $component = Livewire::test(\App\Livewire\Course\Index::class)
        ->set('search', 'Charpente');

    expect($component->viewData('courses')->pluck('id'))->not->toContain($course->id);
});

test('la ricerca video esclude titoli presenti solo in una lingua diversa da quella corrente o italiano', function () {
    $user = User::factory()->create(['locale' => 'en']);
    $video = Video::create(['title' => ['fr' => 'Assemblage poutres']]);

    $this->actingAs($user);
    app()->setLocale('en');

    // Stesso motivo del test analogo per Course: si verifica il risultato della query
    // (assertDontSee non discriminerebbe, il titolo non tradotto è sempre vuoto).
    $component = Livewire::test(\App\Livewire\Video\Index::class)
        ->set('search', 'Assemblage');

    expect($component->viewData('videos')->pluck('id'))->not->toContain($video->id);
});

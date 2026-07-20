<?php

use App\Models\User;

test('preferredLocale ritorna it quando l utente non ha scelto una lingua', function () {
    $user = User::factory()->create();

    expect($user->preferredLocale())->toBe('it');
});

test('preferredLocale ritorna la lingua scelta dall utente', function () {
    $user = User::factory()->create(['locale' => 'fr']);

    expect($user->preferredLocale())->toBe('fr');
});

test('la creazione di un utente non fallisce se manca il ruolo super_admin', function () {
    expect(fn () => User::factory()->create())->not->toThrow(Exception::class);
});

test('la lingua preferita dell utente autenticato viene applicata', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/dashboard');

    expect(app()->getLocale())->toBe('en');
});

test('la lingua in sessione viene applicata agli ospiti', function () {
    $this->withSession(['locale' => 'fr'])->get('/login');

    expect(app()->getLocale())->toBe('fr');
});

test('la lingua del browser viene usata senza preferenze salvate', function () {
    $this->get('/login', ['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.5']);

    expect(app()->getLocale())->toBe('fr');
});

test('una lingua non supportata in sessione ricade su it', function () {
    $this->withSession(['locale' => 'de'])->get('/login');

    expect(app()->getLocale())->toBe('it');
});

test('il cambio lingua viene salvato in sessione per gli ospiti', function () {
    $this->from('/login')->post('/language/en')->assertRedirect('/login');

    expect(session('locale'))->toBe('en');
});

test('il cambio lingua viene salvato sull utente autenticato', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/language/fr');

    expect($user->fresh()->locale)->toBe('fr')
        ->and(session('locale'))->toBe('fr');
});

test('una lingua non supportata restituisce 404', function () {
    $this->post('/language/de')->assertNotFound();
});

test('la sidebar mostra lo switcher lingua', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertSee('Italiano')
        ->assertSee('English')
        ->assertSee('Français');
});

test('la pagina di login mostra lo switcher lingua', function () {
    $this->get('/login')
        ->assertSee('Italiano')
        ->assertSee('English')
        ->assertSee('Français');
});

test('la pagina corsi è tradotta in inglese per un utente inglese', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/courses')
        ->assertSee('Filter by Category')
        ->assertSee('Metal.Ri Courses')
        ->assertSee('was created to promote')
        ->assertDontSee('Metal.Ri Academy nasce con');
});

test('la pagina download è tradotta in francese per un utente francese', function () {
    $user = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($user)->get('/downloads')
        ->assertSee('Filtrer par catégorie');
});

test('i messaggi di validazione sono tradotti in francese', function () {
    app()->setLocale('fr');

    expect(__('validation.required', ['attribute' => 'email']))
        ->not->toBe('validation.required')
        ->toContain('obligatoire');
});

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

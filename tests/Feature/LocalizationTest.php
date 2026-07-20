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

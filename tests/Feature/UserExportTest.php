<?php

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('super_admin');

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin);
});

test('la lista utenti espone l azione di export', function () {
    Livewire::test(ListUsers::class)
        ->assertActionExists('export');
});

test('la lista utenti espone l export sugli utenti selezionati', function () {
    Livewire::test(ListUsers::class)
        ->assertTableBulkActionExists('export');
});

test('l export produce un csv con gli utenti', function () {
    Storage::fake(config('filament.default_filesystem_disk'));

    User::factory()->create(['name' => 'Mario Rossi', 'email' => 'mario@example.test']);

    Livewire::test(ListUsers::class)
        ->callAction('export', data: ['columnMap' => [
            'name' => ['isEnabled' => true, 'label' => 'Nome'],
            'email' => ['isEnabled' => true, 'label' => 'Email'],
            'created_at' => ['isEnabled' => true, 'label' => 'Iscritto il'],
            'last_login_at' => ['isEnabled' => true, 'label' => 'Ultimo accesso'],
        ]]);

    $export = Export::query()->latest('id')->first();

    expect($export)->not->toBeNull()
        ->and($export->successful_rows)->toBe(2);
});

<?php

use App\Filament\Resources\CourseResource;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('l admin apre la pagina di creazione corso', function () {
    Role::create(['name' => 'super_admin']);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(CourseResource::getUrl('create'))
        ->assertSuccessful();
});

<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('impide que un operador actualice los ajustes de la organizacion', function () {
    $org = Organization::factory()->create(['name' => 'Original Name']);
    $operator = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Operator]);

    $response = Livewire::actingAs($operator)
        ->test('pages::organization.settings')
        ->set('name', 'Nuevo Nombre')
        ->call('save');

    $response->assertForbidden();
    $this->assertDatabaseHas('organizations', [
        'id' => $org->id,
        'name' => 'Original Name',
    ]);
});

it('valida que el nombre de la organizacion sea obligatorio', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);

    $response = Livewire::actingAs($admin)
        ->test('pages::organization.settings')
        ->set('name', '')
        ->call('save');

    $response->assertHasErrors(['name' => 'required']);
});

it('permite que un administrador actualice todos los datos corporativos', function () {
    $org = Organization::factory()->create(['name' => 'Old Name']);
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);

    $response = Livewire::actingAs($admin)
        ->test('pages::organization.settings')
        ->set('name', 'Acme Corp')
        ->set('tax_id', '20123456789')
        ->set('email', 'contacto@acme.com')
        ->set('phone', '+51 987654321')
        ->set('address', 'Av. Central 123')
        ->call('save');

    $response->assertHasNoErrors()
        ->assertRedirect('/organizacion/ajustes');
    $this->assertDatabaseHas('organizations', [
        'id' => $org->id,
        'name' => 'Acme Corp',
        'tax_id' => '20123456789',
        'email' => 'contacto@acme.com',
        'phone' => '+51 987654321',
        'address' => 'Av. Central 123',
    ]);
});

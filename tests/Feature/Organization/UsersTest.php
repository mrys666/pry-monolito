<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('permite que un administrador consulte los usuarios de su organizacion', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);
    $operator = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Operator]);

    $response = Livewire::actingAs($admin)->test('pages::organization.users');

    $response->assertOk()
        ->assertSee($admin->name)
        ->assertSee($operator->name);
});

it('impide que un operador acceda a la gestion de usuarios', function () {
    $org = Organization::factory()->create();
    $operator = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Operator]);

    Livewire::actingAs($operator)
        ->test('pages::organization.users')
        ->assertForbidden();
});

it('permite eliminar un usuario sin movimientos de inventario', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);
    $target = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Operator]);

    $response = Livewire::actingAs($admin)
        ->test('pages::organization.users')
        ->call('confirmDelete', $target->id)
        ->call('delete');

    $response->assertHasNoErrors();

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

it('impide eliminar un usuario con movimientos de inventario para preservar la auditoria', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);
    $target = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Operator]);

    $category = Category::create(['organization_id' => $org->id, 'name' => 'General']);
    $product = Product::create([
        'organization_id' => $org->id,
        'category_id' => $category->id,
        'sku' => 'AUD-001',
        'name' => 'Producto auditado',
        'price' => 10,
        'stock' => 5,
    ]);

    InventoryMovement::create([
        'organization_id' => $org->id,
        'product_id' => $product->id,
        'user_id' => $target->id,
        'type' => 'in',
        'quantity' => 5,
        'stock_before' => 0,
        'stock_after' => 5,
        'reason' => 'Carga inicial',
    ]);

    $response = Livewire::actingAs($admin)
        ->test('pages::organization.users')
        ->call('confirmDelete', $target->id)
        ->call('delete');

    $response->assertHasErrors(['delete']);
    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

it('impide que un administrador elimine su propia cuenta', function () {
    $org = Organization::factory()->create();
    $admin = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);

    $response = Livewire::actingAs($admin)
        ->test('pages::organization.users')
        ->call('confirmDelete', $admin->id)
        ->call('delete');

    $response->assertHasErrors(['delete']);
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

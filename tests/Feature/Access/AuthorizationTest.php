<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

it('evalua la matriz de permisos para crear productos y ajustar stock', function (UserRole $role, bool $canManageCatalogAndAdjust, bool $canRegisterFlow) {
    $user = User::factory()->create(['role' => $role]);

    $catalogResult = Gate::forUser($user)->allows('create', Product::class);
    $adjustResult = Gate::forUser($user)->allows('adjustStock', InventoryMovement::class);
    $flowResult = Gate::forUser($user)->allows('registerFlow', InventoryMovement::class);

    expect($catalogResult)->toBe($canManageCatalogAndAdjust)
        ->and($adjustResult)->toBe($canManageCatalogAndAdjust)
        ->and($flowResult)->toBe($canRegisterFlow);
})->with([
    'admin' => [UserRole::Admin, true, true],
    'operator' => [UserRole::Operator, false, true],
    'viewer' => [UserRole::Viewer, false, false],
]);

it('impide que un operador cree productos desde el componente y no altera la base de datos', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);

    // Autenticamos primero para que la categoría se cree con la organización correcta
    $this->actingAs($operator);
    $category = Category::create(['name' => 'Periféricos']);

    $response = Livewire::actingAs($operator)
        ->test('pages::catalog.create-product')
        ->set('sku', 'SEC-001')
        ->set('name', 'Producto No Autorizado')
        ->set('category_id', $category->id)
        ->set('price', '120.00')
        ->set('stock', 10)
        ->set('minimum_stock', 2)
        ->call('save');

    $response->assertForbidden();
    $this->assertDatabaseCount('products', 0);
});

it('permite que un administrador cree un producto y persiste el estado', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // Autenticamos primero para que la categoría se cree con la organización correcta
    $this->actingAs($admin);
    $category = Category::create(['name' => 'Periféricos']);

    $response = Livewire::actingAs($admin)
        ->test('pages::catalog.create-product')
        ->set('sku', 'SEC-002')
        ->set('name', 'Producto Autorizado')
        ->set('category_id', $category->id)
        ->set('price', '150.00')
        ->set('stock', 10)
        ->set('minimum_stock', 2)
        ->call('save');

    // La ruta correcta es /productos
    $response->assertRedirect('/productos');

    $this->assertDatabaseHas('products', [
        'sku' => 'SEC-002',
        'stock' => 10,
    ]);
});

it('evalua la matriz de permisos para gestionar categorías', function (UserRole $role, bool $canManageCategories) {
    $user = User::factory()->create(['role' => $role]);

    // Autenticamos antes de crear la categoría para que el trait
    // BelongsToOrganization resuelva organization_id desde el usuario.
    $this->actingAs($user);
    $category = Category::create(['name' => 'Categoría Test']);

    $createResult = Gate::forUser($user)->allows('create', Category::class);
    $updateResult = Gate::forUser($user)->allows('update', $category);
    $deleteResult = Gate::forUser($user)->allows('delete', $category);

    expect($createResult)->toBe($canManageCategories)
        ->and($updateResult)->toBe($canManageCategories)
        ->and($deleteResult)->toBe($canManageCategories);
})->with([
    'admin' => [UserRole::Admin, true],
    'operator' => [UserRole::Operator, false],
    'viewer' => [UserRole::Viewer, false],
]);

it('permite a cualquier rol consultar el listado de categorías', function (UserRole $role) {
    $user = User::factory()->create(['role' => $role]);

    expect(Gate::forUser($user)->allows('viewAny', Category::class))->toBeTrue();
})->with([
    'admin' => [UserRole::Admin],
    'operator' => [UserRole::Operator],
    'viewer' => [UserRole::Viewer],
]);

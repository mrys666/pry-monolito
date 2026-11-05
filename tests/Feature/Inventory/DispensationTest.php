<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

it('registra una salida y disminuye el stock', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'General']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'TEST-001',
        'name' => 'Producto Prueba',
        'price' => 10,
        'stock' => 20, // Stock inicial
    ]);

    $action = new RegisterDispensationAction;
    $movement = $action->handle($product->id, $user->id, 5, 'Venta regular');

    // Asserts sobre el stock final
    expect($product->fresh()->stock)->toBe(15)
        ->and($movement->stock_before)->toBe(20)
        ->and($movement->stock_after)->toBe(15);
});

it('impide que un visor registre una salida de inventario', function () {
    $org = Organization::factory()->create();
    $viewer = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Viewer]);

    $category = Category::create(['organization_id' => $org->id, 'name' => 'General']);
    $product = Product::create([
        'organization_id' => $org->id,
        'category_id' => $category->id,
        'sku' => 'TEST-003',
        'name' => 'Producto Protegido',
        'price' => 10,
        'stock' => 20,
    ]);

    $this->actingAs($viewer);

    Livewire::test('pages::inventory.register-dispensation')
        ->set('product_id', $product->id)
        ->set('quantity', 5)
        ->call('save')
        ->assertForbidden();

    expect($product->fresh()->stock)->toBe(20);
});

it('lanza excepcion si no hay stock suficiente', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'General']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'TEST-002',
        'name' => 'Producto Sin Stock',
        'price' => 10,
        'stock' => 5,
    ]);

    $action = new RegisterDispensationAction;

    // Intentamos sacar 10, solo hay 5
    $action->handle($product->id, $user->id, 10);
})->throws(InsufficientStockException::class);

<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Category;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Models\User;
use Illuminate\Support\Facades\Concurrency;

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

    $action = new RegisterDispensationAction();
    $movement = $action->handle($product->id, $user->id, 5, 'Venta regular');

    // Asserts sobre el stock final
    expect($product->fresh()->stock)->toBe(15)
        ->and($movement->stock_before)->toBe(20)
        ->and($movement->stock_after)->toBe(15);
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

    $action = new RegisterDispensationAction();

    // Intentamos sacar 10, solo hay 5
    $action->handle($product->id, $user->id, 10);
})->throws(InsufficientStockException::class);

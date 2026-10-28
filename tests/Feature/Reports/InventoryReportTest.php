<?php

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Actions\RegisterEntryAction;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Domain\Reports\Queries\LowStockProductsQuery;
use App\Models\User;

it('calcula correctamente los indicadores del inventario y detecta stock bajo sin duplicar logica', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Hardware']);

    $productOk = Product::create([
        'category_id' => $category->id,
        'sku' => 'REP-001',
        'name' => 'Mouse',
        'price' => 50,
        'stock' => 10,
        'minimum_stock' => 5,
    ]);

    $productLow = Product::create([
        'category_id' => $category->id,
        'sku' => 'REP-002',
        'name' => 'Teclado',
        'price' => 100,
        'stock' => 5,
        'minimum_stock' => 5,
    ]);

    // +5 a Mouse (queda en 15) y -2 a Teclado (queda en 3, crítico)
    (new RegisterEntryAction())->handle($productOk->id, $user->id, 5, 'Compra');
    (new RegisterDispensationAction())->handle($productLow->id, $user->id, 2, 'Venta');

    $summary = (new InventorySummaryQuery())->handle(now()->subDay(), now());
    $lowStockList = (new LowStockProductsQuery())->paginate();

    expect($summary['total_products'])->toBe(2)
        ->and($summary['total_units'])->toBe(18) // 15 + 3
        ->and($summary['total_valuation'])->toBe(1050.0) // (15 * 50) + (3 * 100)
        ->and($summary['low_stock_count'])->toBe(1)
        ->and($summary['units_in'])->toBe(5)
        ->and($summary['units_out'])->toBe(2)
        ->and($lowStockList->total())->toBe(1)
        // Corregido: Usamos items()[0] para evitar el error de método no definido en el paginador
        ->and($lowStockList->items()[0]->sku)->toBe('REP-002')
        ->and($lowStockList->items()[0]->last_movement_at)->not->toBeNull();
});

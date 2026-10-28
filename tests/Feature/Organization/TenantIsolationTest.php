<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Jobs\SendLowStockAlertJob;
use App\Domain\Organization\Models\Organization;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

it('impide que un usuario retire stock de un producto de otra organizacion devolviendo 404 sin modificar la base de datos', function () {
    $orgA = Organization::create(['name' => 'Empresa A']);
    $orgB = Organization::create(['name' => 'Empresa B']);

    $userA = User::factory()->create(['organization_id' => $orgA->id, 'role' => UserRole::Admin]);
    $userB = User::factory()->create(['organization_id' => $orgB->id, 'role' => UserRole::Admin]);

    $this->actingAs($userB);
    $categoryB = Category::create(['name' => 'Categoría B']);
    $productB = Product::create([
        'category_id' => $categoryB->id,
        'sku' => 'TENANT-001',
        'name' => 'Producto Exclusivo B',
        'price' => 100,
        'stock' => 20,
        'minimum_stock' => 5,
    ]);

    Queue::fake([SendLowStockAlertJob::class]);
    $this->actingAs($userA);

    $this->expectException(ModelNotFoundException::class);
    (new RegisterDispensationAction())->handle($productB->id, $userA->id, 5, 'Intento cruzado');

    $this->assertDatabaseHas('products', [
        'id' => $productB->id,
        'stock' => 20,
    ]);
    $this->assertDatabaseCount('inventory_movements', 0);
    Queue::assertNothingPushed();
});

it('permite registrar el mismo SKU en dos organizaciones distintas y aisla los indicadores del reporte', function () {
    $orgA = Organization::create(['name' => 'Empresa A']);
    $orgB = Organization::create(['name' => 'Empresa B']);

    $adminA = User::factory()->create(['organization_id' => $orgA->id, 'role' => UserRole::Admin]);
    $adminB = User::factory()->create(['organization_id' => $orgB->id, 'role' => UserRole::Admin]);

    $this->actingAs($adminB);
    $catB = Category::create(['name' => 'Cat B']);
    Product::create([
        'category_id' => $catB->id,
        'sku' => 'SKU-COMPARTIDO',
        'name' => 'Producto Empresa B',
        'price' => 200,
        'stock' => 50,
        'minimum_stock' => 5,
    ]);

    $this->actingAs($adminA);
    $catA = Category::create(['name' => 'Cat A']);

    $response = Livewire::actingAs($adminA)
        ->test('pages::catalog.create-product')
        ->set('sku', 'SKU-COMPARTIDO')
        ->set('name', 'Producto Empresa A')
        ->set('category_id', $catA->id)
        ->set('price', '80.00')
        ->set('stock', 10)
        ->set('minimum_stock', 2)
        ->call('save');

    $response->assertRedirect('/productos');

    $summaryA = (new InventorySummaryQuery())->handle(now()->subDay(), now());

    expect($summaryA['total_products'])->toBe(1)
        ->and($summaryA['total_units'])->toBe(10)
        ->and($summaryA['total_valuation'])->toBe(800.0);
});

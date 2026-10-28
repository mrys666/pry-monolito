<?php

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Jobs\SendLowStockAlertJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('encola una alerta cuando el stock llega al nivel minimo tras una salida', function () {
    Queue::fake();

    $user = User::factory()->create();
    $category = Category::create(['name' => 'General']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'ALERT-001',
        'name' => 'Producto Crítico',
        'price' => 50,
        'stock' => 10,
        'minimum_stock' => 5,
    ]);

    // Retiramos 6 unidades: el stock queda en 4 (menor al mínimo de 5)
    (new RegisterDispensationAction())->handle($product->id, $user->id, 6, 'Venta mayorista');

    Queue::assertPushed(SendLowStockAlertJob::class, function ($job) use ($product) {
        return $job->product->id === $product->id;
    });
});

it('no encola alerta si el stock resultante sigue por encima del minimo', function () {
    Queue::fake();

    $user = User::factory()->create();
    $category = Category::create(['name' => 'General']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'ALERT-002',
        'name' => 'Producto Abundante',
        'price' => 50,
        'stock' => 20,
        'minimum_stock' => 5,
    ]);

    // Retiramos 5 unidades: el stock queda en 15 (seguro)
    (new RegisterDispensationAction())->handle($product->id, $user->id, 5, 'Venta normal');

    Queue::assertNotPushed(SendLowStockAlertJob::class);
});

<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterEntryAction;
use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Organization\Models\Organization;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;

it('transmite el movimiento por el canal privado exclusivo de la organizacion', function () {
    $org = Organization::create(['name' => 'Empresa Tiempo Real']);
    $user = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);

    $this->actingAs($user);
    $category = Category::create(['name' => 'Redes']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'RT-001',
        'name' => 'Router WiFi 6',
        'price' => 180,
        'stock' => 10,
        'minimum_stock' => 3,
    ]);

    Event::fake([InventoryMovementRegistered::class]);

    (new RegisterEntryAction())->handle($product->id, $user->id, 5, 'Reposición inmediata');

    Event::assertDispatched(InventoryMovementRegistered::class, function (InventoryMovementRegistered $event) use ($org, $product) {
        $channels = $event->broadcastOn();

        return $channels[0] instanceof PrivateChannel
            && $channels[0]->name === "private-organization.{$org->id}"
            && $event->broadcastAs() === InventoryMovementRegistered::BROADCAST_AS
            && $event->broadcastWith()['product_id'] === $product->id
            && $event->broadcastWith()['stock_after'] === 15;
    });
});

it('invalida la cache del reporte inmediatamente cuando se registra un movimiento', function () {
    $org = Organization::create(['name' => 'Empresa Cache']);
    $user = User::factory()->create(['organization_id' => $org->id, 'role' => UserRole::Admin]);

    $this->actingAs($user);
    $category = Category::create(['name' => 'Almacenamiento']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'SSD-001',
        'name' => 'Disco SSD 1TB',
        'price' => 250,
        'stock' => 4,
        'minimum_stock' => 2,
    ]);

    $query = new InventorySummaryQuery();
    $initialSummary = $query->handle(now()->subDay(), now());

    (new RegisterEntryAction())->handle($product->id, $user->id, 6, 'Nueva compra');

    $updatedSummary = $query->handle(now()->subDay(), now());

    expect($initialSummary['total_units'])->toBe(4)
        ->and($updatedSummary['total_units'])->toBe(10);
});

<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Inventory\Jobs\SendLowStockAlertJob;

class CheckLowStockListener
{
    public function handle(InventoryMovementRegistered $event): void
    {
        // Si es una entrada de mercadería, no necesitamos evaluar alerta de escasez
        if ($event->movement->type === 'in') {
            return;
        }

        $product = $event->movement->product;

        // Regla de dominio: ¿El stock resultante cayó en o por debajo del mínimo?
        if ($product->stock <= $product->minimum_stock) {
            SendLowStockAlertJob::dispatch($product);
        }
    }
}

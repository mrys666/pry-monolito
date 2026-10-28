<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdjustStockAction
{
    public function handle(int $productId, int $userId, int $newStock, string $reason): InventoryMovement
    {
        if ($newStock < 0) {
            throw new InvalidArgumentException('El stock ajustado no puede ser negativo.');
        }

        return DB::transaction(function () use ($productId, $userId, $newStock, $reason) {
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $stockBefore = $product->stock;
            $difference = $newStock - $stockBefore;

            if ($difference === 0) {
                throw new InvalidArgumentException('El nuevo stock es igual al actual. No hay ajuste que realizar.');
            }

            $product->update(['stock' => $newStock]);

            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => 'adjustment',
                // Guardamos la cantidad absoluta de la diferencia para el historial
                'quantity' => abs($difference),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                // En un ajuste, el motivo debería ser obligatorio para auditoría
                'reason' => $reason,
            ]);

            // Despachar el evento
            InventoryMovementRegistered::dispatch($movement);

            return $movement;
        });
    }
}

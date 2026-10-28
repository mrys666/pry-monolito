<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegisterEntryAction
{
    public function handle(int $productId, int $userId, int $quantity, ?string $reason = null): InventoryMovement
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad de entrada debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($productId, $userId, $quantity, $reason) {
            // Bloqueamos la fila para asegurar que el stock_before sea exacto
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $stockBefore = $product->stock;
            $stockAfter = $stockBefore + $quantity;

            $product->update(['stock' => $stockAfter]);



            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => 'in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
            ]);

            // Se encola en memoria y se dispara solo cuando DB::transaction hace commit
            // Despachar el evento (Laravel esperará a que termine la transacción)
            InventoryMovementRegistered::dispatch($movement);

            return $movement;
        });
    }
}

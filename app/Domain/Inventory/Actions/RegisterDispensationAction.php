<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class RegisterDispensationAction
{
    /**
     * @throws InsufficientStockException
     */
    public function handle(int $productId, int $userId, int $quantity, ?string $reason = null): InventoryMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($productId, $userId, $quantity, $reason) {
            // lockForUpdate bloquea la fila del producto. Si otro usuario intenta sacar
            // stock simultáneamente, su transacción esperará aquí hasta que esta termine.
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            if ($product->stock < $quantity) {
                throw new InsufficientStockException($quantity, $product->stock);
            }

            $stockBefore = $product->stock;
            $stockAfter = $product->stock - $quantity;

            // Actualizamos el producto
            $product->update(['stock' => $stockAfter]);

            // Dejamos registro inmutable del movimiento
            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
            ]);

            // Se encola en memoria y se dispara solo cuando DB::transaction hace commit
            InventoryMovementRegistered::dispatch($movement);

            return $movement;
        });
    }
}

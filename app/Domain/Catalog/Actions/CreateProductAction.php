<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Product;

class CreateProductAction
{
    /**
     * @param  array{sku: string, name: string, category_id: int, price: numeric-string|float|int, stock: int, minimum_stock: int, is_active?: bool}  $data
     */
    public function handle(array $data): Product
    {
        // Aquí encapsulamos la creación. Si más adelante necesitamos
        // emitir eventos (ej. ProductCreated) o transacciones, lo haremos aquí.
        return Product::create($data);
    }
}

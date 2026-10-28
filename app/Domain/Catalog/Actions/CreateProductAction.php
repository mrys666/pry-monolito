<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Product;

class CreateProductAction
{
    public function handle(array $data): Product
    {
        // Aquí encapsulamos la creación. Si más adelante necesitamos
        // emitir eventos (ej. ProductCreated) o transacciones, lo haremos aquí.
        return Product::create($data);
    }
}

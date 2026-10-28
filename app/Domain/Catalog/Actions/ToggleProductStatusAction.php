<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Product;

class ToggleProductStatusAction
{
    public function handle(Product $product): Product
    {
        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return $product;
    }
}

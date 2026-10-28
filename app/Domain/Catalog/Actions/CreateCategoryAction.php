<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;

class CreateCategoryAction
{
    public function handle(array $data): Category
    {
        // Encapsulamos la creación. Si más adelante necesitamos emitir
        // eventos (ej. CategoryCreated) o transacciones, va aquí.
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}

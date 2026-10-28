<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;

class UpdateCategoryAction
{
    public function handle(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category;
    }
}

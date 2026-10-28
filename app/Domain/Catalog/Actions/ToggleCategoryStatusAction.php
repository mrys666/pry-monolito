<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;

class ToggleCategoryStatusAction
{
    public function handle(Category $category): Category
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return $category;
    }
}

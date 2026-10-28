<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Exceptions\CategoryHasProductsException;
use App\Domain\Catalog\Models\Category;

class DeleteCategoryAction
{
    /**
     * @throws CategoryHasProductsException
     */
    public function handle(Category $category): void
    {
        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            throw new CategoryHasProductsException($category->id, $productsCount);
        }

        $category->delete();
    }
}

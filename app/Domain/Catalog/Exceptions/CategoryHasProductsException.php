<?php

namespace App\Domain\Catalog\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class CategoryHasProductsException extends Exception implements ShouldntReport
{
    public function __construct(public readonly int $categoryId, public readonly int $productsCount)
    {
        parent::__construct(
            "No se puede eliminar la categoría porque tiene {$productsCount} producto(s) asociado(s)."
        );
    }
}

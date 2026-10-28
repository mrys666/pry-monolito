<?php

namespace App\Domain\Reports\Queries;

use App\Domain\Catalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LowStockProductsQuery
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->select(['id', 'category_id', 'sku', 'name', 'stock', 'minimum_stock', 'price'])
            ->active()
            ->lowStock()
            ->with(['category:id,name'])
            ->withLastMovementAt()
            ->orderBy('stock')
            ->orderBy('id') // Desempate determinístico para paginación estable
            ->paginate($perPage);
    }
}

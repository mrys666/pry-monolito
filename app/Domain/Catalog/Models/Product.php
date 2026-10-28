<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Organization\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'category_id',
        'sku',
        'name',
        'price',
        'stock',
        'minimum_stock',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Regla de negocio: Producto activo en el catálogo.
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Regla de negocio: Producto en o por debajo del stock mínimo.
     */
    #[Scope]
    protected function lowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }

    /**
     * Subconsulta correlacionada: Obtiene la fecha del último movimiento sin hidratar la relación.
     */
    #[Scope]
    protected function withLastMovementAt(Builder $query): void
    {
        $query->addSelect([
            'last_movement_at' => InventoryMovement::select('created_at')
                ->whereColumn('product_id', 'products.id')
                ->latest()
                ->take(1),
        ])->withCasts(['last_movement_at' => 'datetime']);
    }
}

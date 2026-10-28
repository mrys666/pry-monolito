<?php

namespace App\Domain\Reports\Queries;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;

class InventorySummaryQuery
{
    public static function bumpVersionForTenant(int|string|null $tenantId): void
    {
        $tenant = $tenantId ?? 'global';
        $versionKey = "reports.version.org_{$tenant}";

        if (! Cache::has($versionKey)) {
            Cache::forever($versionKey, 2);

            return;
        }

        Cache::increment($versionKey);
    }

    public function handle(CarbonInterface $from, CarbonInterface $to): array
    {
        $tenantId = auth()->user()?->organization_id ?? Context::get('organization_id') ?? 'global';
        $version = Cache::get("reports.version.org_{$tenantId}", 1);

        $cacheKey = sprintf(
            'reports.summary.org_%s.v%s.%s.%s',
            $tenantId,
            $version,
            $from->toDateString(),
            $to->toDateString()
        );

        return Cache::flexible($cacheKey, [60, 300], function () use ($from, $to) {
            $catalogStats = Product::active()
                ->toBase()
                ->selectRaw('COUNT(*) as total_products')
                ->selectRaw('COALESCE(SUM(stock), 0) as total_units')
                ->selectRaw('COALESCE(SUM(stock * price), 0) as total_valuation')
                ->selectRaw('COUNT(CASE WHEN stock <= minimum_stock THEN 1 END) as low_stock_count')
                ->first();

            $movementStats = InventoryMovement::query()
                ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                ->toBase()
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END), 0) as units_in")
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END), 0) as units_out")
                ->selectRaw("COUNT(CASE WHEN type = 'adjustment' THEN 1 END) as adjustments_count")
                ->first();

            return [
                'total_products' => (int) $catalogStats->total_products,
                'total_units' => (int) $catalogStats->total_units,
                'total_valuation' => (float) $catalogStats->total_valuation,
                'low_stock_count' => (int) $catalogStats->low_stock_count,
                'units_in' => (int) $movementStats->units_in,
                'units_out' => (int) $movementStats->units_out,
                'adjustments_count' => (int) $movementStats->adjustments_count,
            ];
        });
    }
}

<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Domain\Reports\Queries\LowStockProductsQuery;
use App\Support\InventoryComponent;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends InventoryComponent {
    use WithPagination;

    public ?int $organizationId = null;

    public function mount(): void
    {
        $this->organizationId = auth()->user()?->organization_id;
    }

    #[On('echo-private:organization.{organizationId},InventoryMovementRegistered')]
    public function refreshDashboard(): void
    {
        if ($this->organizationId === null) {
            return;
        }

        $cacheKey = sprintf('reports.summary.org_%s.%s.%s', $this->organizationId, now()->startOfMonth()->toDateString(), now()->toDateString());
        Cache::forget($cacheKey);
    }

    public function with(InventorySummaryQuery $summaryQuery, LowStockProductsQuery $lowStockQuery): array
    {
        $from = now()->startOfMonth();
        $to = now();

        $summary = $summaryQuery->handle($from, $to);

        $health = $summary['total_products'] > 0 ? (int) round((($summary['total_products'] - $summary['low_stock_count']) / $summary['total_products']) * 100) : 100;

        return [
            'summary' => $summary,
            'health' => max(0, min(100, $health)),
            'lowStockProducts' => $lowStockQuery->paginate(5),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl space-y-8">
    <x-page-header eyebrow="Panel de control" title="Buenos días, {{ auth()->user()->name }}"
        description="Este es el pulso de tu inventario durante {{ now()->startOfMonth()->format('d/m/Y') }} — {{ now()->format('d/m/Y') }}."
        icon="squares-2x2">
        <flux:button href="{{ route('reports.index') }}" variant="outline" icon="chart-bar" wire:navigate>
            Ver reportes
        </flux:button>

        @can('registerFlow', InventoryMovement::class)
            <flux:button href="{{ route('inventory.entry.create') }}" variant="primary" icon="plus" wire:navigate>
                Registrar entrada
            </flux:button>
        @endcan
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Productos activos" :value="number_format($summary['total_products'])" description="En el catálogo actual" icon="cube"
            color="sky" />

        <x-stat-card label="Existencias totales" :value="number_format($summary['total_units']) . ' un.'"
            description="Valorizadas en S/ {{ number_format($summary['total_valuation'], 2) }}" icon="archive-box"
            color="emerald" />

        <x-stat-card label="Flujo del mes" :value="number_format($summary['units_in'] + $summary['units_out']) . ' un.'"
            description="{{ $summary['adjustments_count'] }} ajuste(s) manual(es)" icon="arrow-path" color="violet">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium">
                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                    <flux:icon.arrow-down class="size-3.5" />
                    +{{ number_format($summary['units_in']) }} entradas
                </span>
                <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400">
                    <flux:icon.arrow-up class="size-3.5" />
                    -{{ number_format($summary['units_out']) }} salidas
                </span>
            </div>
        </x-stat-card>

        <x-stat-card label="Productos en alerta" :value="number_format($summary['low_stock_count'])" description="En o por debajo del stock mínimo"
            icon="exclamation-triangle" :color="$summary['low_stock_count'] > 0 ? 'amber' : 'emerald'">
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>Salud del inventario</span>
                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $health }}%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full transition-all duration-500 {{ $health >= 80 ? 'bg-emerald-500' : ($health >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                        style="width: {{ $health }}%"></div>
                </div>
            </div>
        </x-stat-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <flux:card class="overflow-hidden p-0 lg:col-span-2">
            <div
                class="flex flex-col gap-3 border-b border-zinc-200/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                <div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">Productos que requieren atención</flux:heading>
                        @if ($summary['low_stock_count'] > 0)
                            <flux:badge color="amber" size="sm">{{ $summary['low_stock_count'] }}</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Ordenados por nivel de stock. Repón los más críticos primero.
                    </flux:text>
                </div>

                @can('registerFlow', InventoryMovement::class)
                    <flux:button href="{{ route('inventory.entry.create') }}" variant="subtle" size="sm"
                        icon="plus" wire:navigate>
                        Reponer stock
                    </flux:button>
                @endcan
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Producto</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">Categoría</flux:table.column>
                    <flux:table.column>Stock</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">Último movimiento</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($lowStockProducts as $product)
                        <flux:table.row wire:key="low-stock-{{ $product->id }}">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                        <flux:icon.cube class="size-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-zinc-900 dark:text-white">
                                            {{ $product->name }}</p>
                                        <p class="text-xs text-zinc-400">{{ $product->sku }}</p>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                <flux:badge color="zinc" size="sm">
                                    {{ $product->category->name ?? 'Sin categoría' }}</flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="w-32 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span
                                            class="font-semibold text-amber-600 dark:text-amber-400">{{ $product->stock }}
                                            un.</span>
                                        <span class="text-zinc-400">mín. {{ $product->minimum_stock }}</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <div class="h-full rounded-full bg-amber-500"
                                            style="width: {{ $product->minimum_stock > 0 ? min(100, round(($product->stock / max(1, $product->minimum_stock)) * 100)) : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="hidden text-sm text-zinc-500 lg:table-cell dark:text-zinc-400">
                                {{ $product->last_movement_at ? $product->last_movement_at->format('d/m/Y H:i') : 'Sin movimientos' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
                                <x-empty-state icon="check-circle" color="emerald" title="Todo en orden"
                                    description="Ningún producto está por debajo de su stock mínimo. Tu inventario está bajo control." />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($lowStockProducts->hasPages())
                <div class="border-t border-zinc-200/70 px-5 py-4 dark:border-zinc-800">
                    {{ $lowStockProducts->links() }}
                </div>
            @endif
        </flux:card>

        <div class="space-y-6">
            <flux:card class="space-y-4 p-5">
                <div class="flex items-center gap-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-lg bg-zinc-500/10 text-zinc-600 dark:bg-zinc-400/10 dark:text-zinc-300">
                        <flux:icon.bolt class="size-4" />
                    </span>
                    <div>
                        <flux:heading size="sm">Accesos rápidos</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Atajos para las tareas del día</flux:text>
                    </div>
                </div>

                <div class="grid gap-2">
                    @can('registerFlow', InventoryMovement::class)
                        <flux:button href="{{ route('inventory.entry.create') }}" variant="outline" icon="plus"
                            class="justify-start" wire:navigate>
                            Registrar entrada
                        </flux:button>
                        <flux:button href="{{ route('inventory.dispensation.create') }}" variant="outline" icon="minus"
                            class="justify-start" wire:navigate>
                            Registrar salida
                        </flux:button>
                    @endcan

                    @can('adjustStock', InventoryMovement::class)
                        <flux:button href="{{ route('inventory.adjustment.create') }}" variant="outline"
                            icon="arrows-up-down" class="justify-start" wire:navigate>
                            Ajustar stock
                        </flux:button>
                    @endcan

                    @can('create', Product::class)
                        <flux:button href="{{ route('catalog.products.create') }}" variant="outline" icon="cube"
                            class="justify-start" wire:navigate>
                            Nuevo producto
                        </flux:button>
                    @endcan
                </div>
            </flux:card>

            <flux:card class="space-y-3 p-5">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">Estado del inventario</flux:heading>
                    <flux:badge :color="$health >= 80 ? 'emerald' : ($health >= 50 ? 'amber' : 'red')" size="sm">
                        {{ $health }}% saludable
                    </flux:badge>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full transition-all duration-500 {{ $health >= 80 ? 'bg-emerald-500' : ($health >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                        style="width: {{ $health }}%"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-950/40">
                        <p class="text-xs text-zinc-500">Unidades</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($summary['total_units']) }}</p>
                    </div>
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-950/40">
                        <p class="text-xs text-zinc-500">Valor</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">S/
                            {{ number_format($summary['total_valuation'], 0) }}</p>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>

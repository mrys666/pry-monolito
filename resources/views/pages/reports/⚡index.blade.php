<?php

use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Domain\Reports\Queries\LowStockProductsQuery;
use App\Support\InventoryComponent;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends InventoryComponent {
    use WithPagination;

    public string $period = '30d';

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function with(InventorySummaryQuery $summaryQuery, LowStockProductsQuery $lowStockQuery): array
    {
        [$from, $to] = match ($this->period) {
            '7d' => [now()->subDays(6), now()],
            'month' => [now()->startOfMonth(), now()],
            default => [now()->subDays(29), now()],
        };

        $summary = $summaryQuery->handle($from, $to);

        $health = $summary['total_products'] > 0
            ? (int) round((($summary['total_products'] - $summary['low_stock_count']) / $summary['total_products']) * 100)
            : 100;

        return [
            'summary' => $summary,
            'health' => max(0, min(100, $health)),
            'from' => $from,
            'to' => $to,
            'lowStockProducts' => $lowStockQuery->paginate(10),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl space-y-8">
    <x-page-header eyebrow="Análisis" title="Reportes e indicadores"
        description="Del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }} · Resumen operativo y estado de existencias."
        icon="chart-bar">
        <flux:radio.group wire:model.live="period" variant="segmented" size="sm">
            <flux:radio value="7d" label="7 días" />
            <flux:radio value="30d" label="30 días" />
            <flux:radio value="month" label="Este mes" />
        </flux:radio.group>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Existencias totales" :value="number_format($summary['total_units']) . ' un.'"
            description="En {{ $summary['total_products'] }} producto(s) activo(s)" icon="archive-box" color="sky" />

        <x-stat-card label="Valorización" :value="'S/ ' . number_format($summary['total_valuation'], 2)"
            description="Precio de venta estimado" icon="banknotes" color="emerald" />

        <x-stat-card label="Flujo del periodo"
            :value="number_format($summary['units_in'] + $summary['units_out']) . ' un.'"
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

        <x-stat-card label="Productos en alerta" :value="number_format($summary['low_stock_count'])"
            description="En o por debajo del stock mínimo" icon="exclamation-triangle"
            :color="$summary['low_stock_count'] > 0 ? 'amber' : 'emerald'">
            <flux:badge :color="$summary['low_stock_count'] > 0 ? 'amber' : 'emerald'" size="sm">
                {{ $summary['low_stock_count'] > 0 ? 'Requiere atención' : 'Sin alertas' }}
            </flux:badge>
        </x-stat-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <flux:card class="space-y-5 p-5 lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <flux:heading size="lg">Flujo del periodo</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Comparativo de unidades que entran y salen del inventario.
                    </flux:text>
                </div>
                <flux:badge color="zinc" size="sm">{{ $from->format('d/m') }} — {{ $to->format('d/m') }}</flux:badge>
            </div>

            @php
                $maxFlow = max(1, $summary['units_in'], $summary['units_out']);
                $entriesWidth = $summary['units_in'] > 0 ? max(4, round(($summary['units_in'] / $maxFlow) * 100)) : 0;
                $outputsWidth = $summary['units_out'] > 0 ? max(4, round(($summary['units_out'] / $maxFlow) * 100)) : 0;
            @endphp

            <div class="space-y-5">
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <flux:icon.arrow-down class="size-4" />
                            </span>
                            Entradas
                        </span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">+{{ number_format($summary['units_in']) }} un.</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $entriesWidth }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 font-medium text-zinc-700 dark:text-zinc-200">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-red-500/10 text-red-600 dark:text-red-400">
                                <flux:icon.arrow-up class="size-4" />
                            </span>
                            Salidas
                        </span>
                        <span class="font-semibold text-red-600 dark:text-red-400">-{{ number_format($summary['units_out']) }} un.</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-red-500 transition-all duration-500" style="width: {{ $outputsWidth }}%"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/40">
                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <flux:icon.arrows-up-down class="size-4" />
                        </span>
                        Ajustes manuales
                    </div>
                    <span class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $summary['adjustments_count'] }}</span>
                </div>
            </div>
        </flux:card>

        <flux:card class="flex flex-col p-5">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">Salud del inventario</flux:heading>
                <flux:badge :color="$health >= 80 ? 'emerald' : ($health >= 50 ? 'amber' : 'red')" size="sm">
                    {{ $health }}%
                </flux:badge>
            </div>

            <p class="mt-4 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                {{ max(0, $summary['total_products'] - $summary['low_stock_count']) }}
                <span class="text-sm font-normal text-zinc-500">de {{ $summary['total_products'] }} productos</span>
            </p>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Se encuentran por encima del stock mínimo.
            </flux:text>

            <flux:progress :value="$health" :color="$health >= 80 ? 'emerald' : ($health >= 50 ? 'amber' : 'red')" class="mt-4 h-2" />

            <div class="mt-auto space-y-2 pt-6">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Unidades en stock</span>
                    <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($summary['total_units']) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">Productos en alerta</span>
                    <span class="font-medium {{ $summary['low_stock_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $summary['low_stock_count'] }}
                    </span>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="overflow-hidden p-0">
        <div class="flex flex-col gap-3 border-b border-zinc-200/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">Productos con stock crítico</flux:heading>
                    @if ($summary['low_stock_count'] > 0)
                        <flux:badge color="amber" size="sm">{{ $summary['low_stock_count'] }}</flux:badge>
                    @endif
                </div>
                <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                    Prioriza el reabastecimiento de estos productos.
                </flux:text>
            </div>

            @can('registerFlow', InventoryMovement::class)
                <flux:button href="{{ route('inventory.entry.create') }}" variant="primary" size="sm" icon="plus"
                    wire:navigate>
                    Reabastecer
                </flux:button>
            @endcan
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column class="hidden md:table-cell">Categoría</flux:table.column>
                <flux:table.column>Stock actual / mínimo</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Último movimiento</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($lowStockProducts as $product)
                    <flux:table.row wire:key="low-stock-{{ $product->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                    <flux:icon.cube class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $product->name }}</p>
                                    <p class="text-xs text-zinc-400">{{ $product->sku }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="hidden md:table-cell">
                            <flux:badge color="zinc" size="sm">{{ $product->category->name ?? 'Sin categoría' }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:badge color="red" size="sm">{{ $product->stock }} un.</flux:badge>
                                <span class="text-xs text-zinc-400">mín. {{ $product->minimum_stock }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="hidden text-sm text-zinc-500 lg:table-cell dark:text-zinc-400">
                            {{ $product->last_movement_at ? $product->last_movement_at->format('d/m/Y H:i') : 'Sin movimientos' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <x-empty-state icon="check-circle" color="emerald" title="Sin stock crítico"
                                description="Excelente: ningún producto activo se encuentra por debajo de su stock mínimo en este periodo." />
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
</div>

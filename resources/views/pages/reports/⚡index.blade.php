<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Domain\Reports\Queries\LowStockProductsQuery;
use App\Support\Concerns\ListensForInventoryUpdates;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, ListensForInventoryUpdates;

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

        return [
            'summary' => $summaryQuery->handle($from, $to),
            'lowStockProducts' => $lowStockQuery->paginate(10),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-8 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl">Reportes e Indicadores</flux:heading>
            <flux:text class="text-gray-500">Resumen operativo y estado de existencias del inventario.</flux:text>
        </div>

        <div class="w-48">
            <flux:select wire:model.live="period">
                <flux:select.option value="7d">Últimos 7 días</flux:select.option>
                <flux:select.option value="30d">Últimos 30 días</flux:select.option>
                <flux:select.option value="month">Este mes</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Tarjetas de Indicadores (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Existencias Totales</flux:text>
            <flux:heading size="xl">{{ number_format($summary['total_units']) }} un.</flux:heading>
            <flux:text class="text-xs text-gray-400">
                En {{ $summary['total_products'] }} productos activos
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Valorización del Inventario</flux:text>
            <flux:heading size="xl">S/ {{ number_format($summary['total_valuation'], 2) }}</flux:heading>
            <flux:text class="text-xs text-gray-400">Precio de venta estimado</flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Flujo del Periodo</flux:text>
            <div class="flex items-baseline gap-4">
                <span class="text-lg font-semibold text-green-600">+{{ number_format($summary['units_in']) }}</span>
                <span class="text-lg font-semibold text-red-600">-{{ number_format($summary['units_out']) }}</span>
            </div>
            <flux:text class="text-xs text-gray-400">
                Ajustes manuales: {{ $summary['adjustments_count'] }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Productos en Alerta</flux:text>
            <flux:heading size="xl"
                class="{{ $summary['low_stock_count'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $summary['low_stock_count'] }}
            </flux:heading>
            <flux:text class="text-xs text-gray-400">En o por debajo del stock mínimo</flux:text>
        </flux:card>
    </div>

    <!-- Tabla de Productos con Stock Bajo -->
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Productos con Stock Crítico</flux:heading>
            <flux:button href="/inventario/entrada" variant="primary" size="sm" icon="plus" wire:navigate>
                Reabastecer
            </flux:button>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column>Categoría</flux:table.column>
                <flux:table.column>Stock Actual / Mínimo</flux:table.column>
                <flux:table.column>Último Movimiento</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($lowStockProducts as $product)
                    <flux:table.row wire:key="low-stock-{{ $product->id }}">
                        <flux:table.cell class="font-medium">{{ $product->sku }}</flux:table.cell>
                        <flux:table.cell>{{ $product->name }}</flux:table.cell>
                        <flux:table.cell>{{ $product->category->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="danger">
                                {{ $product->stock }} / {{ $product->minimum_stock }} un.
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-gray-500">
                            {{ $product->last_movement_at ? $product->last_movement_at->format('d/m/Y H:i') : 'Sin movimientos' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-gray-500 py-6">
                            Excelente: Ningún producto activo se encuentra por debajo de su stock mínimo.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $lowStockProducts->links() }}
        </div>
    </flux:card>
</div>

<script>
    this.$intercept('handleRealTimeInventoryUpdate', ({
        onSuccess
    }) => {
        onSuccess(() => {
            console.log('Inventario sincronizado en tiempo real vía Reverb.');
        });
    });
</script>

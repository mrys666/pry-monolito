<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Reports\Queries\InventorySummaryQuery;
use App\Domain\Reports\Queries\LowStockProductsQuery;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public function with(InventorySummaryQuery $summaryQuery, LowStockProductsQuery $lowStockQuery): array
    {
        // Calculamos el periodo para el flujo (mes actual)
        $from = now()->startOfMonth();
        $to = now();

        return [
            'summary' => $summaryQuery->handle($from, $to),
            'lowStockProducts' => $lowStockQuery->paginate(5),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-8 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <flux:heading size="xl">Panel de Control</flux:heading>
            <flux:text class="text-gray-500">Resumen operativo de tu organización.</flux:text>
        </div>
        <flux:button href="{{ route('reports.index') }}" variant="primary" icon="chart-bar" wire:navigate>
            Ver Reportes Detallados
        </flux:button>
    </div>

    <!-- Tarjetas de Indicadores (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- KPI 1: Productos Totales -->
        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Productos Activos</flux:text>
            <flux:heading size="xl">{{ number_format($summary['total_products']) }}</flux:heading>
            <flux:text class="text-xs text-gray-400">En el catálogo actual</flux:text>
        </flux:card>

        <!-- KPI 2: Unidades en Stock -->
        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Existencias Totales</flux:text>
            <flux:heading size="xl">{{ number_format($summary['total_units']) }} <span
                    class="text-sm font-normal text-gray-500">un.</span></flux:heading>
            <flux:text class="text-xs text-gray-400">Valorizado en ${{ number_format($summary['total_valuation'], 2) }}
            </flux:text>
        </flux:card>

        <!-- KPI 3: Movimientos del Mes -->
        <flux:card class="space-y-2">
            <flux:text class="text-sm text-gray-500">Flujo del Mes</flux:text>
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-semibold text-green-600">+{{ number_format($summary['units_in']) }}</span>
                <span class="text-lg font-semibold text-red-600">-{{ number_format($summary['units_out']) }}</span>
            </div>
            <flux:text class="text-xs text-gray-400">{{ $summary['adjustments_count'] }} ajustes manuales</flux:text>
        </flux:card>

        <!-- KPI 4: Alertas de Stock -->
        <flux:card class="space-y-2 border-red-200 dark:border-red-900/50">
            <flux:text class="text-sm text-gray-500">Productos en Alerta</flux:text>
            <flux:heading size="xl"
                class="{{ $summary['low_stock_count'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($summary['low_stock_count']) }}
            </flux:heading>
            <flux:text class="text-xs text-gray-400">En o por debajo del stock mínimo</flux:text>
        </flux:card>
    </div>

    <!-- Tabla de Productos con Stock Crítico -->
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Productos que requieren atención</flux:heading>
            <flux:button href="{{ route('inventory.entry.create') }}" variant="subtle" size="sm" icon="plus"
                wire:navigate>
                Registrar Entrada
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
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.check-circle class="size-8 text-green-500" />
                                <span>¡Todo en orden! Ningún producto está por debajo de su stock mínimo.</span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($lowStockProducts->hasPages())
            <div class="mt-4">
                {{ $lowStockProducts->links() }}
            </div>
        @endif
    </flux:card>
</div>

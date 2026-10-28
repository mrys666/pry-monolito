<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Domain\Inventory\Models\InventoryMovement;
use App\Support\Concerns\ListensForInventoryUpdates;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, ListensForInventoryUpdates;

    public function with(): array
    {
        return [
            // Eager loading para producto y usuario
            'movements' => InventoryMovement::with(['product', 'user'])
                ->latest()
                ->paginate(15),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    <!-- Cabecera adaptable: se apila en móviles y se alinea en pantallas medianas -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <flux:heading size="xl">Movimientos de Inventario</flux:heading>

        <!-- Botones de acción adaptables (ocupan todo el ancho en móviles para facilitar el toque) -->
        <div class="grid grid-cols-3 sm:flex sm:items-center gap-2 sm:gap-3">
            <flux:button href="/inventario/entrada" variant="primary" icon="plus" wire:navigate class="justify-center">
                Entrada
            </flux:button>
            <flux:button href="/inventario/salida" variant="danger" icon="minus" wire:navigate class="justify-center">
                Salida
            </flux:button>
            <flux:button href="/inventario/ajuste" variant="subtle" icon="arrows-up-down" wire:navigate
                class="justify-center">
                Ajuste
            </flux:button>
        </div>
    </div>

    <flux:card class="overflow-hidden p-0 sm:p-6">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column>Cant.</flux:table.column>
                <!-- Columnas ocultas en dispositivos móviles pequeños para evitar desbordamiento -->
                <flux:table.column class="hidden md:table-cell">Balance</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Usuario</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($movements as $movement)
                    <flux:table.row wire:key="movement-row-{{ $movement->id }}">
                        <flux:table.cell class="text-gray-500 text-xs sm:text-sm whitespace-nowrap">
                            {{ $movement->created_at->format('d/m/Y H:i') }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            <div class="truncate max-w-[150px] sm:max-w-xs"
                                title="{{ $movement->product?->name ?? 'Producto eliminado' }}">
                                {{ $movement->product?->name ?? 'Producto eliminado' }}
                            </div>
                            <span class="text-gray-400 text-xs block">{{ $movement->product?->sku ?? 'N/A' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($movement->type === 'out')
                                <flux:badge variant="danger" size="sm">Salida</flux:badge>
                            @elseif($movement->type === 'in')
                                <flux:badge variant="success" size="sm">Entrada</flux:badge>
                            @else
                                <flux:badge variant="warning" size="sm">Ajuste</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell
                            class="font-semibold {{ $movement->type === 'out' ? 'text-red-600' : ($movement->type === 'in' ? 'text-green-600' : 'text-yellow-600') }}">
                            {{ $movement->type === 'out' ? '-' : '+' }}{{ $movement->quantity }}
                        </flux:table.cell>
                        <!-- Celdas ocultas responsivas que coinciden con las columnas -->
                        <flux:table.cell class="text-gray-600 hidden md:table-cell text-sm">
                            {{ $movement->stock_before }} &rarr; {{ $movement->stock_after }}
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-gray-500 hidden lg:table-cell">
                            {{ $movement->user->name ?? 'Sistema' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-gray-500 py-8">
                            Aún no hay movimientos registrados en el inventario.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4 px-4 sm:px-0">
            {{ $movements->links() }}
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

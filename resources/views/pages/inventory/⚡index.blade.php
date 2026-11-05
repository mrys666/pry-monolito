<?php

use App\Domain\Inventory\Models\InventoryMovement;
use App\Support\InventoryComponent;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends InventoryComponent {
    use WithPagination;

    public string $search = '';
    public string $type = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'type');
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'movements' => InventoryMovement::with(['product', 'user'])
                ->when($this->search !== '', function ($query) {
                    $query->whereHas('product', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%")
                            ->orWhere('sku', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
                ->latest()
                ->paginate(15),
            'typeCounts' => InventoryMovement::query()
                ->selectRaw('type, COUNT(*) as aggregate')
                ->groupBy('type')
                ->pluck('aggregate', 'type'),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header eyebrow="Inventario" title="Movimientos"
        description="Audita cada entrada, salida y ajuste de stock de tu organización." icon="arrow-path-rounded-square">
        @can('adjustStock', InventoryMovement::class)
            <flux:button href="{{ route('inventory.adjustment.create') }}" variant="subtle" icon="arrows-up-down"
                wire:navigate>
                Ajustar stock
            </flux:button>
        @endcan

        @can('registerFlow', InventoryMovement::class)
            <flux:button href="{{ route('inventory.dispensation.create') }}" variant="danger" icon="minus"
                wire:navigate>
                Salida
            </flux:button>
            <flux:button href="{{ route('inventory.entry.create') }}" variant="primary" icon="plus" wire:navigate>
                Entrada
            </flux:button>
        @endcan
    </x-page-header>

    <div class="flex flex-wrap items-center gap-2">
        <flux:badge color="zinc" size="sm" icon="queue-list">
            {{ $movements->total() }} movimiento(s)
        </flux:badge>
        <flux:badge color="emerald" size="sm" icon="arrow-down">
            {{ number_format($typeCounts['in'] ?? 0) }} entradas
        </flux:badge>
        <flux:badge color="red" size="sm" icon="arrow-up">
            {{ number_format($typeCounts['out'] ?? 0) }} salidas
        </flux:badge>
        <flux:badge color="amber" size="sm" icon="arrows-up-down">
            {{ number_format($typeCounts['adjustment'] ?? 0) }} ajustes
        </flux:badge>
    </div>

    <flux:card class="space-y-4 p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    placeholder="Buscar producto o SKU..." class="sm:max-w-xs" />

                <flux:select wire:model.live="type" class="sm:max-w-52">
                    <flux:select.option value="">Todos los movimientos</flux:select.option>
                    <flux:select.option value="in">Entradas</flux:select.option>
                    <flux:select.option value="out">Salidas</flux:select.option>
                    <flux:select.option value="adjustment">Ajustes</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center gap-2">
                <span wire:loading class="inline-flex items-center gap-1.5 text-xs text-zinc-400">
                    <flux:icon.arrow-path class="size-3.5 animate-spin" />
                    Actualizando
                </span>

                @if ($search !== '' || $type !== '')
                    <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="resetFilters">
                        Limpiar filtros
                    </flux:button>
                @endif
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="hidden sm:table-cell">Fecha</flux:table.column>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column>Tipo</flux:table.column>
                <flux:table.column class="text-right">Cantidad</flux:table.column>
                <flux:table.column class="hidden md:table-cell">Balance</flux:table.column>
                <flux:table.column class="hidden xl:table-cell">Motivo</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Usuario</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($movements as $movement)
                    <flux:table.row wire:key="movement-row-{{ $movement->id }}">
                        <flux:table.cell class="hidden whitespace-nowrap text-sm text-zinc-500 sm:table-cell dark:text-zinc-400">
                            <time datetime="{{ $movement->created_at->toIso8601String() }}">
                                {{ $movement->created_at->format('d/m/Y') }}
                                <span class="block text-xs text-zinc-400">{{ $movement->created_at->format('H:i') }}</span>
                            </time>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-500/10 text-zinc-500 dark:bg-zinc-400/10 dark:text-zinc-300">
                                    <flux:icon.cube class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-zinc-900 dark:text-white">
                                        {{ $movement->product?->name ?? 'Producto eliminado' }}
                                    </p>
                                    <p class="text-xs text-zinc-400">{{ $movement->product?->sku ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($movement->type === 'out')
                                <flux:badge color="red" size="sm" icon="arrow-up">Salida</flux:badge>
                            @elseif ($movement->type === 'in')
                                <flux:badge color="emerald" size="sm" icon="arrow-down">Entrada</flux:badge>
                            @else
                                <flux:badge color="amber" size="sm" icon="arrows-up-down">Ajuste</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <span class="font-semibold {{ $movement->type === 'out' ? 'text-red-600 dark:text-red-400' : ($movement->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">
                                {{ $movement->type === 'out' ? '-' : ($movement->type === 'in' ? '+' : '±') }}{{ number_format($movement->quantity) }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="hidden md:table-cell">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-zinc-50 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-950/40 dark:text-zinc-300">
                                {{ $movement->stock_before }}
                                <flux:icon.arrow-right class="size-3 text-zinc-400" />
                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $movement->stock_after }}</span>
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="hidden max-w-48 xl:table-cell">
                            <p class="truncate text-sm text-zinc-500 dark:text-zinc-400" title="{{ $movement->reason }}">
                                {{ $movement->reason ?: 'Sin motivo registrado' }}
                            </p>
                        </flux:table.cell>

                        <flux:table.cell class="hidden lg:table-cell">
                            <div class="flex items-center gap-2">
                                <flux:avatar size="xs" :name="$movement->user?->name ?? 'Sistema'"
                                    :initials="$movement->user?->initials() ?? 'S'" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $movement->user?->name ?? 'Sistema' }}
                                </span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <x-empty-state icon="arrow-path-rounded-square" title="Sin movimientos"
                                description="No hay movimientos que coincidan con los filtros. Registra una entrada o salida para comenzar.">
                                @can('registerFlow', InventoryMovement::class)
                                    <flux:button href="{{ route('inventory.entry.create') }}" variant="primary" size="sm"
                                        icon="plus" wire:navigate>
                                        Registrar entrada
                                    </flux:button>
                                @endcan
                            </x-empty-state>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="border-t border-zinc-200/70 pt-4 dark:border-zinc-800">
            {{ $movements->links() }}
        </div>
    </flux:card>
</div>

<?php

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Support\InventoryComponent;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends InventoryComponent {
    use WithPagination;

    public string $search = '';
    public string $categoryId = '';
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'categoryId', 'status');
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'products' => Product::query()
                ->with('category')
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($query) {
                        $query->where('name', 'like', "%{$this->search}%")
                            ->orWhere('sku', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->categoryId !== '', fn ($query) => $query->where('category_id', $this->categoryId))
                ->when($this->status === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock'))
                ->when($this->status === 'active', fn ($query) => $query->where('is_active', true))
                ->when($this->status === 'inactive', fn ($query) => $query->where('is_active', false))
                ->latest()
                ->paginate(10),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header eyebrow="Catálogo" title="Productos"
        description="Consulta el catálogo de tu organización y detecta rápidamente el estado de cada producto."
        icon="cube">
        <flux:button href="{{ route('catalog.categories.index') }}" variant="outline" icon="tag" wire:navigate>
            Categorías
        </flux:button>

        @can('create', Product::class)
            <flux:button href="{{ route('catalog.products.create') }}" variant="primary" icon="plus" wire:navigate>
                Nuevo producto
            </flux:button>
        @endcan
    </x-page-header>

    <flux:card class="space-y-4 p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    placeholder="Buscar por nombre o SKU..." class="sm:max-w-xs" />

                <flux:select wire:model.live="categoryId" class="sm:max-w-48">
                    <flux:select.option value="">Todas las categorías</flux:select.option>
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="status" class="sm:max-w-44">
                    <flux:select.option value="">Todos los estados</flux:select.option>
                    <flux:select.option value="active">Activos</flux:select.option>
                    <flux:select.option value="inactive">Inactivos</flux:select.option>
                    <flux:select.option value="low">Stock bajo</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-between gap-3 lg:justify-end">
                <span wire:loading class="inline-flex items-center gap-1.5 text-xs text-zinc-400">
                    <flux:icon.arrow-path class="size-3.5 animate-spin" />
                    Actualizando
                </span>

                @if ($search !== '' || $categoryId !== '' || $status !== '')
                    <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="resetFilters">
                        Limpiar filtros
                    </flux:button>
                @endif

                <flux:badge color="zinc" size="sm">{{ $products->total() }} producto(s)</flux:badge>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column class="hidden md:table-cell">Categoría</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">Precio</flux:table.column>
                <flux:table.column>Stock</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($products as $product)
                    <flux:table.row wire:key="product-row-{{ $product->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-500/10 text-zinc-500 dark:bg-zinc-400/10 dark:text-zinc-300">
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

                        <flux:table.cell class="hidden font-medium sm:table-cell">
                            S/ {{ number_format($product->price, 2) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:badge :color="$product->stock <= $product->minimum_stock ? 'red' : 'emerald'" size="sm">
                                    {{ $product->stock }} un.
                                </flux:badge>
                                @if ($product->stock <= $product->minimum_stock)
                                    <flux:icon.exclamation-triangle class="size-3.5 text-amber-500" />
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] text-zinc-400">mín. {{ $product->minimum_stock }}</p>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$product->is_active ? 'emerald' : 'zinc'" size="sm">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <x-empty-state icon="cube" title="Sin productos"
                                description="No encontramos productos con los filtros seleccionados. Ajusta la búsqueda o crea el primero.">
                                @can('create', Product::class)
                                    <flux:button href="{{ route('catalog.products.create') }}" variant="primary" size="sm"
                                        icon="plus" wire:navigate>
                                        Nuevo producto
                                    </flux:button>
                                @endcan
                            </x-empty-state>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="border-t border-zinc-200/70 pt-4 dark:border-zinc-800">
            {{ $products->links() }}
        </div>
    </flux:card>
</div>

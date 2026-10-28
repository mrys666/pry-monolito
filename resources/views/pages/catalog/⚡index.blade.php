<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Models\Product;
use App\Support\Concerns\ListensForInventoryUpdates;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, ListensForInventoryUpdates;

    public function with(): array
    {
        return [
            // Evitamos el problema N+1 cargando la relación 'category'
            'products' => Product::with('category')->latest()->paginate(10),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-8 space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Catálogo de Productos</flux:heading>
        <flux:button href="/productos/crear" variant="primary" icon="plus" wire:navigate>
            Nuevo Producto
        </flux:button>
    </div>

    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column>Categoría</flux:table.column>
                <flux:table.column>Precio</flux:table.column>
                <flux:table.column>Stock</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($products as $product)
                    <flux:table.row wire:key="product-row-{{ $product->id }}">
                        <flux:table.cell class="font-medium text-gray-900">{{ $product->sku }}</flux:table.cell>
                        <flux:table.cell>{{ $product->name }}</flux:table.cell>
                        <flux:table.cell>{{ $product->category->name ?? 'Sin categoría' }}</flux:table.cell>
                        <flux:table.cell>S/ {{ number_format($product->price, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                variant="{{ $product->stock <= $product->minimum_stock ? 'danger' : 'success' }}">
                                {{ $product->stock }} un.
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="{{ $product->is_active ? 'success' : 'secondary' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-gray-500 py-4">
                            No hay productos registrados en el catálogo.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $products->links() }}
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

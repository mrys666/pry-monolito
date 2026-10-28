<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\AdjustStockAction;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use App\Domain\Inventory\Models\InventoryMovement;

new #[Layout('layouts.app')] class extends Component {
    public ?int $product_id = null;
    public ?int $new_stock = null;
    public string $reason = '';

    public function save(AdjustStockAction $action)
    {
        Gate::authorize('adjustStock', InventoryMovement::class);

        $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:255'], // Motivo obligatorio
        ]);

        try {
            $action->handle(productId: $this->product_id, userId: auth()->id(), newStock: $this->new_stock, reason: $this->reason);

            session()->flash('status', 'Stock ajustado correctamente.');
            $this->redirect('/inventario', navigate: true);
        } catch (\InvalidArgumentException $e) {
            // Atrapa el error si envían el mismo stock que ya existe
            throw ValidationException::withMessages([
                'new_stock' => $e->getMessage(),
            ]);
        }
    }

    public function with(): array
    {
        return [
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ];
    }
};
?>

<div class="max-w-3xl mx-auto py-8">
    <flux:heading size="xl" class="mb-6">Ajustar Stock Físico</flux:heading>

    <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
        <p class="text-sm text-yellow-800">
            <strong>Atención:</strong> Utiliza este formulario únicamente tras un conteo de inventario físico. El valor
            que ingreses reemplazará el stock actual del sistema.
        </p>
    </div>

    <form wire:submit="save" class="space-y-6  p-6 rounded-xl border border-gray-200">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:field class="md:col-span-2">
                <flux:label>Producto</flux:label>
                <flux:select wire:model="product_id" placeholder="Seleccione un producto...">
                    <flux:select.option selected>Seleccione un producto...</flux:select.option>
                    @foreach ($products as $product)
                        <flux:select.option value="{{ $product->id }}">
                            {{ $product->sku }} - {{ $product->name }} (Stock en sistema: {{ $product->stock }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="product_id" />
            </flux:field>

            <flux:field>
                <flux:label>Nuevo Stock Físico (Real)</flux:label>
                <flux:input type="number" wire:model="new_stock" placeholder="Ej: 18" min="0" />
                <flux:error name="new_stock" />
            </flux:field>

            <flux:field class="md:col-span-2">
                <flux:label>Motivo del Ajuste (Obligatorio)</flux:label>
                <flux:textarea wire:model="reason"
                    placeholder="Ej: Inventario general de fin de mes, producto caducado encontrado en bodega..." />
                <flux:error name="reason" />
            </flux:field>
        </div>

        <flux:separator class="my-6" />

        <div class="flex justify-end gap-4">
            <flux:button href="/inventario" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="danger" icon="exclamation-triangle">Aplicar Ajuste</flux:button>
        </div>
    </form>
</div>

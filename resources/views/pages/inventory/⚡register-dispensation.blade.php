<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app')] class extends Component {
    public ?int $product_id = null;
    public ?int $quantity = null;
    public string $reason = '';

    public function save(RegisterDispensationAction $action)
    {
        $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            // Ejecutamos el caso de uso pasando los datos validados
            $action->handle(
                productId: $this->product_id,
                userId: auth()->id(), // Usamos el ID del usuario autenticado
                quantity: $this->quantity,
                reason: $this->reason ?: null,
            );

            $this->reset();

            // Mensaje de éxito temporal antes de redirigir
            session()->flash('status', 'Salida registrada correctamente.');
            $this->redirect('/inventario', navigate: true);
        } catch (InsufficientStockException $e) {
            // Transformamos la Excepción de Dominio en un error de validación visual
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        }
    }

    public function with(): array
    {
        return [
            // Cargamos solo los productos activos para el selector
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ];
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-6">Registrar Salida de Inventario</flux:heading>

    <form wire:submit="save" class="space-y-6  p-6 rounded-xl border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:field class="md:col-span-2">
                <flux:label>Producto</flux:label>
                <flux:select wire:model="product_id" placeholder="Seleccione un producto...">
                    <flux:select.option selected>Seleccione un producto...</flux:select.option>

                    @foreach ($products as $product)
                        <flux:select.option value="{{ $product->id }}">
                            {{ $product->sku }} - {{ $product->name }} (Stock actual: {{ $product->stock }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="product_id" />
            </flux:field>

            <flux:field>
                <flux:label>Cantidad a retirar</flux:label>
                <flux:input type="number" wire:model="quantity" placeholder="Ej: 5" min="1" />
                <flux:error name="quantity" />
            </flux:field>

            <flux:field class="md:col-span-2">
                <flux:label>Motivo de la salida (Opcional)</flux:label>
                <flux:textarea wire:model="reason" placeholder="Ej: Venta regular, merma, uso interno..." />
                <flux:error name="reason" />
            </flux:field>
        </div>

        <flux:separator class="my-6" />

        <div class="flex justify-end gap-4">
            <flux:button href="/inventario" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary">Registrar Salida</flux:button>
        </div>
    </form>
</div>

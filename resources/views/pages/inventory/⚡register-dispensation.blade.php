<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\RegisterDispensationAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public ?int $product_id = null;
    public ?int $quantity = null;
    public string $reason = '';

    public function save(RegisterDispensationAction $action): void
    {
        Gate::authorize('registerFlow', InventoryMovement::class);

        $organizationId = auth()->user()->organization_id;

        $this->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $organizationId)],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $action->handle(
                productId: $this->product_id,
                userId: auth()->id(),
                quantity: $this->quantity,
                reason: $this->reason ?: null,
            );

            $this->reset();

            session()->flash('status', 'Salida registrada correctamente.');
            $this->redirect('/inventario', navigate: true);
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
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

<div class="mx-auto max-w-3xl space-y-6">
    <x-page-header eyebrow="Inventario" title="Registrar salida"
        description="Descuenta unidades del stock y deja registro del motivo de la salida." icon="arrow-up">
        <flux:button href="{{ route('inventory.index') }}" variant="subtle" icon="arrow-left" wire:navigate>
            Volver a movimientos
        </flux:button>
    </x-page-header>

    <flux:callout variant="warning" icon="exclamation-triangle">
        Verifica la cantidad antes de confirmar. El sistema no permitirá retirar más unidades de las disponibles.
    </flux:callout>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-5 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-600 dark:bg-red-400/10 dark:text-red-400">
                    <flux:icon.arrow-up class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Datos de la salida</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Selecciona el producto y la cantidad a retirar.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field class="md:col-span-2">
                    <flux:label>Producto</flux:label>
                    <flux:select wire:model="product_id" placeholder="Seleccione un producto...">
                        <flux:select.option selected>Seleccione un producto...</flux:select.option>
                        @foreach ($products as $product)
                            <flux:select.option value="{{ $product->id }}">
                                {{ $product->sku }} — {{ $product->name }} (Disponible: {{ $product->stock }})
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
                    <flux:label>Motivo de la salida (opcional)</flux:label>
                    <flux:textarea wire:model="reason" placeholder="Ej: Venta regular, merma, uso interno..." />
                    <flux:error name="reason" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('inventory.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="danger" icon="minus" wire:loading.attr="disabled" wire:target="save">
                Registrar salida
            </flux:button>
        </div>
    </form>
</div>

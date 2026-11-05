<?php

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Actions\AdjustStockAction;
use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public ?int $product_id = null;
    public ?int $new_stock = null;
    public string $reason = '';

    public function save(AdjustStockAction $action): void
    {
        Gate::authorize('adjustStock', InventoryMovement::class);

        $organizationId = auth()->user()->organization_id;

        $this->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where('organization_id', $organizationId)],
            'new_stock' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            $action->handle(productId: $this->product_id, userId: auth()->id(), newStock: $this->new_stock, reason: $this->reason);

            session()->flash('status', 'Stock ajustado correctamente.');
            $this->redirect('/inventario', navigate: true);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'new_stock' => $exception->getMessage(),
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
    <x-page-header eyebrow="Inventario" title="Ajustar stock"
        description="Corrige el stock del sistema tras un conteo físico. Queda registrado para auditoría."
        icon="arrows-up-down">
        <flux:button href="{{ route('inventory.index') }}" variant="subtle" icon="arrow-left" wire:navigate>
            Volver a movimientos
        </flux:button>
    </x-page-header>

    <flux:callout variant="warning" icon="exclamation-triangle">
        <flux:callout.heading>Usa este formulario solo tras un conteo físico</flux:callout.heading>
        <flux:callout.text>
            El valor que ingreses reemplazará el stock actual del sistema. El motivo es obligatorio para mantener la
            trazabilidad.
        </flux:callout.text>
    </flux:callout>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-5 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <flux:icon.arrows-up-down class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Datos del ajuste</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Indica el nuevo stock físico y el motivo del ajuste.
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
                                {{ $product->sku }} — {{ $product->name }} (Stock en sistema: {{ $product->stock }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="product_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Nuevo stock físico (real)</flux:label>
                    <flux:input type="number" wire:model="new_stock" placeholder="Ej: 18" min="0" />
                    <flux:error name="new_stock" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Motivo del ajuste (obligatorio)</flux:label>
                    <flux:textarea wire:model="reason"
                        placeholder="Ej: Inventario general de fin de mes, producto caducado encontrado en bodega..." />
                    <flux:error name="reason" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('inventory.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="danger" icon="exclamation-triangle" wire:loading.attr="disabled"
                wire:target="save">
                Aplicar ajuste
            </flux:button>
        </div>
    </form>
</div>

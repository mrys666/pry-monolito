<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Actions\CreateProductAction;
use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Facades\Gate;
use App\Domain\Catalog\Models\Product;
use Illuminate\Validation\Rule;

new #[Layout('layouts.app')] class extends Component {
    public string $sku = '';
    public string $name = '';
    public string $price = '';
    public ?int $category_id = null;
    public int $stock = 0;
    public int $minimum_stock = 0;

    public function save(CreateProductAction $action)
    {
        $orgId = auth()->user()->organization_id;

        Gate::authorize('create', Product::class);

        $validated = $this->validate([
            'sku' => ['required', 'string', Rule::unique('products', 'sku')->where('organization_id', $orgId)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('organization_id', $orgId)],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ]);

        // Inyección de dependencias en el método para ejecutar el caso de uso
        $action->handle($validated);

        $this->reset();

        // Asumiendo que usas notificaciones nativas de Flux UI si están configuradas,
        // de lo contrario, redirigimos al catálogo.
        $this->redirect('/productos', navigate: true);
    }

    public function with(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ];
    }
};
?>

<div>
    <flux:heading size="xl" class="mb-6">Registrar Nuevo Producto</flux:heading>

    <form wire:submit="save" class="space-y-6  p-6 rounded-xl border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:field>
                <flux:label>SKU (Código único)</flux:label>
                <flux:input wire:model="sku" placeholder="Ej: PROD-001" />
                <flux:error name="sku" />
            </flux:field>

            <flux:field>
                <flux:label>Nombre del Producto</flux:label>
                <flux:input wire:model="name" placeholder="Ej: Teclado Mecánico" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Categoría</flux:label>
                <flux:select wire:model="category_id" placeholder="Seleccione una categoría...">
                    <flux:select.option selected>Seleccione una categoría...</flux:select.option>
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="category_id" />
            </flux:field>

            <flux:field>
                <flux:label>Precio de Venta</flux:label>
                <flux:input type="number" step="0.01" wire:model="price" placeholder="0.00" />
                <flux:error name="price" />
            </flux:field>

            <flux:field>
                <flux:label>Stock Inicial</flux:label>
                <flux:input type="number" wire:model="stock" placeholder="0" />
                <flux:error name="stock" />
            </flux:field>

            <flux:field>
                <flux:label>Stock Mínimo (Alerta)</flux:label>
                <flux:input type="number" wire:model="minimum_stock" placeholder="0" />
                <flux:error name="minimum_stock" />
            </flux:field>
        </div>

        <flux:separator class="my-6" />

        <div class="flex justify-end gap-4">
            <flux:button href="/productos" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="plus">Guardar Producto</flux:button>
        </div>
    </form>
</div>

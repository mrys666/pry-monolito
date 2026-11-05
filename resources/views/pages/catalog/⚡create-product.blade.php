<?php

use App\Domain\Catalog\Actions\CreateProductAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $sku = '';
    public string $name = '';
    public string $price = '';
    public ?int $category_id = null;
    public int $stock = 0;
    public int $minimum_stock = 0;

    public function save(CreateProductAction $action): void
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

        $action->handle($validated);

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

<div class="mx-auto max-w-3xl space-y-6">
    <x-page-header eyebrow="Catálogo" title="Nuevo producto"
        description="Registra un producto con su código, precio y niveles de stock." icon="cube">
        <flux:button href="{{ route('catalog.index') }}" variant="subtle" icon="arrow-left" wire:navigate>
            Volver al catálogo
        </flux:button>
    </x-page-header>

    <flux:callout variant="secondary" icon="information-circle">
        El SKU debe ser único dentro de tu organización y se usará para identificar el producto en los movimientos.
    </flux:callout>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-6 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400">
                    <flux:icon.cube class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Datos del producto</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Información básica y clasificación.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field>
                    <flux:label>SKU (código único)</flux:label>
                    <flux:input wire:model="sku" placeholder="Ej: PROD-001" autofocus />
                    <flux:error name="sku" />
                </flux:field>

                <flux:field>
                    <flux:label>Nombre del producto</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Teclado mecánico" />
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
                    <flux:label>Precio de venta</flux:label>
                    <flux:input type="number" step="0.01" wire:model="price" placeholder="0.00" />
                    <flux:error name="price" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-6 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <flux:icon.archive-box class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Niveles de stock</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Define el stock inicial y el umbral de alerta.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field>
                    <flux:label>Stock inicial</flux:label>
                    <flux:input type="number" wire:model="stock" placeholder="0" min="0" />
                    <flux:error name="stock" />
                </flux:field>

                <flux:field>
                    <flux:label>Stock mínimo (alerta)</flux:label>
                    <flux:input type="number" wire:model="minimum_stock" placeholder="0" min="0" />
                    <flux:description>Te avisaremos cuando el stock llegue a este nivel.</flux:description>
                    <flux:error name="minimum_stock" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('catalog.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="plus" wire:loading.attr="disabled" wire:target="save">
                Guardar producto
            </flux:button>
        </div>
    </form>
</div>

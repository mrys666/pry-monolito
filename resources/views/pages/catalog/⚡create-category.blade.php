<?php

use App\Domain\Catalog\Actions\CreateCategoryAction;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $name = '';
    public string $description = '';

    public function save(CreateCategoryAction $action): void
    {
        $orgId = auth()->user()->organization_id;

        Gate::authorize('create', Category::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->where('organization_id', $orgId)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->handle($validated);

        Flux::toast(variant: 'success', text: 'Categoría creada correctamente.');
        $this->redirect('/categorias', navigate: true);
    }
};
?>

<div class="mx-auto max-w-2xl space-y-6">
    <x-page-header eyebrow="Catálogo" title="Nueva categoría"
        description="Agrupa productos relacionados para facilitar su búsqueda y análisis." icon="tag">
        <flux:button href="{{ route('catalog.categories.index') }}" variant="subtle" icon="arrow-left" wire:navigate>
            Volver a categorías
        </flux:button>
    </x-page-header>

    <form wire:submit="save" class="space-y-6">
        <flux:card class="space-y-5 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400">
                    <flux:icon.tag class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Datos de la categoría</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        El nombre debe ser único dentro de tu organización.
                    </flux:text>
                </div>
            </div>

            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="name" placeholder="Ej: Bebidas" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Descripción</flux:label>
                <flux:textarea wire:model="description" placeholder="Descripción opcional de la categoría..." rows="3" />
                <flux:description>Ayuda a tu equipo a identificar qué productos pertenecen aquí.</flux:description>
                <flux:error name="description" />
            </flux:field>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('catalog.categories.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="plus" wire:loading.attr="disabled" wire:target="save">
                Guardar categoría
            </flux:button>
        </div>
    </form>
</div>

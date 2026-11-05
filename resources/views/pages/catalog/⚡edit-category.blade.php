<?php

use App\Domain\Catalog\Actions\UpdateCategoryAction;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public Category $category;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
    }

    public function save(UpdateCategoryAction $action): void
    {
        Gate::authorize('update', $this->category);

        $orgId = auth()->user()->organization_id;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->where('organization_id', $orgId)->ignore($this->category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $action->handle($this->category, $validated);

        Flux::toast(variant: 'success', text: 'Categoría actualizada correctamente.');
        $this->redirect('/categorias', navigate: true);
    }
};
?>

<div class="mx-auto max-w-2xl space-y-6">
    <x-page-header eyebrow="Catálogo" title="Editar categoría"
        description="Actualiza el nombre, la descripción o el estado de la categoría." icon="tag">
        <flux:badge :color="$category->is_active ? 'emerald' : 'zinc'" size="sm">
            {{ $category->is_active ? 'Activa' : 'Inactiva' }}
        </flux:badge>
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
                        Los cambios se aplican a todos los productos asociados.
                    </flux:text>
                </div>
            </div>

            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="name" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Descripción</flux:label>
                <flux:textarea wire:model="description" rows="3" />
                <flux:error name="description" />
            </flux:field>

            <flux:field variant="inline">
                <flux:switch wire:model="is_active" />
                <div>
                    <flux:label>Categoría activa</flux:label>
                    <flux:description>Las categorías inactivas no estarán disponibles al crear productos.</flux:description>
                </div>
            </flux:field>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('catalog.categories.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">
                Guardar cambios
            </flux:button>
        </div>
    </form>
</div>

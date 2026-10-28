<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Actions\CreateCategoryAction;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    <flux:heading size="xl">Registrar Nueva Categoría</flux:heading>

    <form wire:submit="save" class="space-y-6 p-6 rounded-xl border border-gray-200">
        <flux:field>
            <flux:label>Nombre</flux:label>
            <flux:input wire:model="name" placeholder="Ej: Bebidas" autofocus />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Descripción</flux:label>
            <flux:textarea wire:model="description" placeholder="Descripción opcional de la categoría..." rows="3" />
            <flux:error name="description" />
        </flux:field>

        <flux:separator class="my-6" />

        <div class="flex justify-end gap-4">
            <flux:button href="/categorias" variant="subtle" wire:navigate>
                Cancelar
            </flux:button>
            <flux:button type="submit" variant="primary" icon="plus">
                Guardar Categoría
            </flux:button>
        </div>
    </form>
</div>

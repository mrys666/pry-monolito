<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Actions\UpdateCategoryAction;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

new #[Layout('layouts.app')] class extends Component {
    public Category $category;
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(Category $category): void
    {
        // Route model binding ya aplica OrganizationScope:
        // un usuario de otra org recibirá 404 automáticamente.
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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Editar Categoría</flux:heading>
        <flux:badge variant="{{ $category->is_active ? 'success' : 'secondary' }}">
            {{ $category->is_active ? 'Activa' : 'Inactiva' }}
        </flux:badge>
    </div>

    <form wire:submit="save" class="space-y-6 p-6 rounded-xl border border-gray-200">
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
            <flux:label>Categoría activa</flux:label>
        </flux:field>

        <flux:separator class="my-6" />

        <div class="flex justify-end gap-4">
            <flux:button href="/categorias" variant="subtle" wire:navigate>
                Cancelar
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                Guardar Cambios
            </flux:button>
        </div>
    </form>
</div>

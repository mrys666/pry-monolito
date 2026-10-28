<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Actions\ToggleCategoryStatusAction;
use App\Domain\Catalog\Exceptions\CategoryHasProductsException;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public function toggleStatus(int $categoryId, ToggleCategoryStatusAction $action): void
    {
        $category = Category::findOrFail($categoryId);
        Gate::authorize('update', $category);

        $action->handle($category);

        Flux::toast(variant: 'success', text: 'Estado actualizado.');
    }

    public function delete(int $categoryId, DeleteCategoryAction $action): void
    {
        $category = Category::findOrFail($categoryId);
        Gate::authorize('delete', $category);

        try {
            $action->handle($category);
            Flux::toast(variant: 'success', text: 'Categoría eliminada.');
        } catch (CategoryHasProductsException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            // withCount hace una subquery — no viola preventLazyLoading
            'categories' => Category::withCount('products')->orderBy('name')->paginate(10),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <flux:heading size="xl">Categorías</flux:heading>

        @can('create', Category::class)
            <flux:button href="/categorias/crear" variant="primary" icon="plus" wire:navigate>
                Nueva Categoría
            </flux:button>
        @endcan
    </div>

    <flux:card class="overflow-hidden p-0 sm:p-6">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column class="hidden md:table-cell">Descripción</flux:table.column>
                <flux:table.column>Productos</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                @can('create', Category::class)
                    <flux:table.column class="text-right">Acciones</flux:table.column>
                @endcan
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categories as $category)
                    <flux:table.row wire:key="category-row-{{ $category->id }}">
                        <flux:table.cell class="font-medium text-gray-900">
                            {{ $category->name }}
                        </flux:table.cell>
                        <flux:table.cell class="hidden md:table-cell text-gray-600">
                            <div class="truncate max-w-xs" title="{{ $category->description }}">
                                {{ $category->description ?? '—' }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="secondary" size="sm">
                                {{ $category->products_count }} un.
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                            </flux:badge>
                        </flux:table.cell>

                        @can('create', Category::class)
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-1">
                                    @can('update', $category)
                                        <flux:tooltip content="Editar">
                                            <flux:button size="sm" variant="subtle" icon="pencil"
                                                href="/categorias/{{ $category->id }}/editar" wire:navigate />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ $category->is_active ? 'Desactivar' : 'Activar' }}">
                                            <flux:button size="sm" variant="subtle" icon="arrow-path"
                                                wire:click="toggleStatus({{ $category->id }})" wire:loading.attr="disabled"
                                                wire:target="toggleStatus" />
                                        </flux:tooltip>
                                    @endcan
                                    @can('delete', $category)
                                        <flux:tooltip content="Eliminar">
                                            <flux:button size="sm" variant="danger" icon="trash"
                                                wire:click="delete({{ $category->id }})"
                                                wire:confirm="¿Estás seguro de eliminar esta categoría?"
                                                wire:loading.attr="disabled" wire:target="delete" />
                                        </flux:tooltip>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        @endcan
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-gray-500 py-8">
                            Aún no hay categorías registradas.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4 px-4 sm:px-0">
            {{ $categories->links() }}
        </div>
    </flux:card>
</div>

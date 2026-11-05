<?php

use App\Domain\Catalog\Actions\DeleteCategoryAction;
use App\Domain\Catalog\Actions\ToggleCategoryStatusAction;
use App\Domain\Catalog\Exceptions\CategoryHasProductsException;
use App\Domain\Catalog\Models\Category;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public function toggleStatus(int $categoryId, ToggleCategoryStatusAction $action): void
    {
        $category = Category::findOrFail($categoryId);
        Gate::authorize('update', $category);

        $action->handle($category);

        Flux::toast(variant: 'success', text: 'Estado de la categoría actualizado.');
    }

    public function delete(int $categoryId, DeleteCategoryAction $action): void
    {
        $category = Category::findOrFail($categoryId);
        Gate::authorize('delete', $category);

        try {
            $action->handle($category);
            Flux::toast(variant: 'success', text: 'Categoría eliminada.');
        } catch (CategoryHasProductsException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'categories' => Category::withCount('products')->orderBy('name')->paginate(10),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header eyebrow="Catálogo" title="Categorías"
        description="Organiza tus productos en categorías para mantener un catálogo claro y fácil de consultar."
        icon="tag">
        @can('create', Category::class)
            <flux:button href="{{ route('catalog.categories.create') }}" variant="primary" icon="plus" wire:navigate>
                Nueva categoría
            </flux:button>
        @endcan
    </x-page-header>

    <flux:card class="overflow-hidden p-0">
        <div class="flex items-center justify-between gap-3 border-b border-zinc-200/70 px-5 py-4 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <flux:heading size="lg">Listado de categorías</flux:heading>
                <flux:badge color="zinc" size="sm">{{ $categories->total() }}</flux:badge>
            </div>
            <flux:text class="hidden text-sm text-zinc-500 sm:block dark:text-zinc-400">
                Las categorías inactivas no aparecen al crear productos.
            </flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Categoría</flux:table.column>
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
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400">
                                    <flux:icon.tag class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $category->name }}</p>
                                    <p class="text-xs text-zinc-400">Categoría #{{ $category->id }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="hidden max-w-xs md:table-cell">
                            <p class="truncate text-sm text-zinc-500 dark:text-zinc-400" title="{{ $category->description }}">
                                {{ $category->description ?: 'Sin descripción' }}
                            </p>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm" icon="cube">
                                {{ $category->products_count }} producto(s)
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$category->is_active ? 'emerald' : 'zinc'" size="sm">
                                {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                            </flux:badge>
                        </flux:table.cell>

                        @can('create', Category::class)
                            <flux:table.cell class="text-right">
                                <div class="flex justify-end gap-1">
                                    @can('update', $category)
                                        <flux:tooltip content="Editar categoría">
                                            <flux:button size="sm" variant="subtle" icon="pencil"
                                                href="{{ route('catalog.categories.edit', $category) }}" wire:navigate />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ $category->is_active ? 'Desactivar categoría' : 'Activar categoría' }}">
                                            <flux:button size="sm" variant="subtle" icon="arrow-path"
                                                wire:click="toggleStatus({{ $category->id }})"
                                                wire:loading.attr="disabled" wire:target="toggleStatus" />
                                        </flux:tooltip>
                                    @endcan
                                    @can('delete', $category)
                                        <flux:tooltip content="Eliminar categoría">
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
                        <flux:table.cell colspan="5">
                            <x-empty-state icon="tag" title="Sin categorías"
                                description="Aún no hay categorías registradas. Crea la primera para organizar tu catálogo.">
                                @can('create', Category::class)
                                    <flux:button href="{{ route('catalog.categories.create') }}" variant="primary" size="sm"
                                        icon="plus" wire:navigate>
                                        Nueva categoría
                                    </flux:button>
                                @endcan
                            </x-empty-state>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="border-t border-zinc-200/70 px-5 py-4 dark:border-zinc-800">
            {{ $categories->links() }}
        </div>
    </flux:card>
</div>

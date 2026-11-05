<?php

use App\Domain\Access\Enums\UserRole;
use App\Domain\Organization\Actions\CreateOrganizationUserAction;
use App\Domain\Organization\Actions\DeleteOrganizationUserAction;
use App\Domain\Organization\Exceptions\UserHasInventoryMovementsException;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public bool $showCreateModal = false;
    public bool $showDeleteModal = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'operator';
    public ?int $userToDelete = null;
    public ?string $userToDeleteName = null;

    public function mount(): void
    {
        Gate::authorize('manage', $this->currentOrganization());
    }

    public function create(CreateOrganizationUserAction $action): void
    {
        $organization = $this->currentOrganization();
        Gate::authorize('manage', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        $action->handle($organization, $validated);

        $this->reset(['name', 'email', 'password', 'role', 'showCreateModal']);

        Flux::toast(variant: 'success', text: 'Usuario creado correctamente.');
    }

    public function confirmDelete(int $id): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $this->userToDelete = $user->id;
        $this->userToDeleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function delete(DeleteOrganizationUserAction $action): void
    {
        Gate::authorize('manage', $this->currentOrganization());

        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($this->userToDelete);

        if ($user->id === auth()->id()) {
            $this->addError('delete', 'No puedes eliminar tu propia cuenta.');

            return;
        }

        try {
            $action->handle($user);
        } catch (UserHasInventoryMovementsException $exception) {
            $this->addError('delete', $exception->getMessage());

            return;
        }

        $this->reset(['userToDelete', 'userToDeleteName', 'showDeleteModal']);

        Flux::toast(variant: 'success', text: 'Usuario eliminado correctamente.');
    }

    public function with(): array
    {
        return [
            'users' => User::where('organization_id', auth()->user()->organization_id)
                ->withCount('movements')
                ->latest()
                ->paginate(10),
        ];
    }

    private function currentOrganization(): Organization
    {
        return Organization::findOrFail(auth()->user()->organization_id);
    }
};
?>

<div class="mx-auto max-w-7xl space-y-6">
    <x-page-header eyebrow="Organización" title="Usuarios"
        description="Administra quién puede acceder a tu organización y qué acciones puede realizar."
        icon="users">
        <flux:button variant="primary" icon="user-plus" wire:click="$set('showCreateModal', true)">
            Nuevo usuario
        </flux:button>
    </x-page-header>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="flex items-start gap-3 rounded-xl border border-zinc-200/80 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <flux:icon.shield-check class="size-4" />
            </span>
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">Administrador</p>
                <p class="text-xs text-zinc-500">Acceso total, catálogo y organización.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-xl border border-zinc-200/80 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                <flux:icon.user class="size-4" />
            </span>
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">Operador</p>
                <p class="text-xs text-zinc-500">Registra entradas y salidas de inventario.</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-xl border border-zinc-200/80 bg-white p-3.5 dark:border-zinc-800 dark:bg-zinc-900">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-500/10 text-zinc-600 dark:text-zinc-300">
                <flux:icon.eye class="size-4" />
            </span>
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">Visor</p>
                <p class="text-xs text-zinc-500">Consulta catálogo y reportes sin editar.</p>
            </div>
        </div>
    </div>

    <flux:card class="overflow-hidden p-0">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Usuario</flux:table.column>
                <flux:table.column>Rol</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">Actividad</flux:table.column>
                <flux:table.column class="text-right">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$user->name" :initials="$user->initials()" />
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                        @if ($user->id === auth()->id())
                                            <flux:badge color="sky" size="sm">Tú</flux:badge>
                                        @endif
                                    </div>
                                    <p class="truncate text-xs text-zinc-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="match ($user->role) {
                                UserRole::Admin => 'emerald',
                                UserRole::Operator => 'sky',
                                UserRole::Viewer => 'zinc',
                            }" size="sm">
                                {{ match ($user->role) {
                                    UserRole::Admin => 'Administrador',
                                    UserRole::Operator => 'Operador',
                                    UserRole::Viewer => 'Visor',
                                } }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="hidden sm:table-cell">
                            @if ($user->movements_count > 0)
                                <flux:badge color="zinc" size="sm" icon="lock-closed">
                                    {{ $user->movements_count }} movimiento(s)
                                </flux:badge>
                            @else
                                <span class="text-sm text-zinc-400">Sin movimientos</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            @if ($user->id !== auth()->id())
                                @if ($user->movements_count > 0)
                                    <flux:tooltip content="Conserva el historial de auditoría. No se puede eliminar.">
                                        <flux:button variant="subtle" size="sm" icon="lock-closed" disabled />
                                    </flux:tooltip>
                                @else
                                    <flux:button variant="danger" size="sm" icon="trash"
                                        wire:click="confirmDelete({{ $user->id }})" />
                                @endif
                            @else
                                <flux:badge color="zinc" size="sm">Sesión actual</flux:badge>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="border-t border-zinc-200/70 px-5 py-4 dark:border-zinc-800">
            {{ $users->links() }}
        </div>
    </flux:card>

    <!-- Modal Crear Usuario -->
    <flux:modal wire:model="showCreateModal" class="max-w-lg">
        <form wire:submit="create" class="space-y-5">
            <div>
                <flux:heading size="lg">Nuevo usuario</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    Crea credenciales de acceso para un miembro de tu organización.
                </flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field class="sm:col-span-2">
                    <flux:label>Nombre completo</flux:label>
                    <flux:input wire:model="name" placeholder="Ej. Ana Torres" autofocus />
                    <flux:error name="name" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Correo electrónico</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="ana@empresa.com"
                        autocomplete="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Contraseña temporal</flux:label>
                    <flux:input type="password" wire:model="password" viewable autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres" />
                    <flux:description>El usuario podrá cambiarla desde su perfil.</flux:description>
                    <flux:error name="password" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Rol de acceso</flux:label>
                    <flux:select wire:model="role">
                        <flux:select.option value="admin">Administrador — acceso total</flux:select.option>
                        <flux:select.option value="operator">Operador — movimientos de inventario</flux:select.option>
                        <flux:select.option value="viewer">Visor — solo consulta</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="subtle" wire:click="$set('showCreateModal', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="user-plus">Crear usuario</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Eliminar Usuario -->
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <form wire:submit="delete" class="space-y-5">
            <div>
                <flux:heading size="lg">¿Eliminar usuario?</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    Vas a eliminar a <span class="font-medium text-zinc-900 dark:text-white">{{ $userToDeleteName }}</span>.
                    Esta acción revoca su acceso inmediatamente.
                </flux:text>
            </div>

            <flux:callout variant="danger" icon="exclamation-triangle">
                Si el usuario tiene movimientos de inventario, no podrá eliminarse para conservar la auditoría.
            </flux:callout>

            <flux:error name="delete" />

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="subtle" wire:click="$set('showDeleteModal', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="danger" icon="trash">Sí, eliminar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

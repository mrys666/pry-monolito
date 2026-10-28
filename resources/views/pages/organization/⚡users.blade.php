<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Domain\Access\Enums\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Domain\Organization\Actions\CreateOrganizationUserAction;
use App\Domain\Organization\Actions\DeleteOrganizationUserAction;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public bool $showCreateModal = false;
    public bool $showDeleteModal = false;

    // Form data
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'operator';
    public ?int $userToDelete = null;

    public function create(CreateOrganizationUserAction $action)
    {
        $organization = auth()->user()->organization;
        Gate::authorize('manage', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        $action->handle($organization, $validated);

        $this->reset(['name', 'email', 'password', 'role', 'showCreateModal']);
        session()->flash('status', 'Usuario creado exitosamente.');
    }

    public function confirmDelete(int $id)
    {
        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function delete(DeleteOrganizationUserAction $action)
    {
        Gate::authorize('manage', auth()->user()->organization);

        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($this->userToDelete);

        // Prevenir auto-eliminación
        if ($user->id === auth()->id()) {
            $this->addError('delete', 'No puedes eliminar tu propia cuenta.');
            return;
        }

        $action->handle($user);
        $this->reset(['userToDelete', 'showDeleteModal']);
    }

    public function with(): array
    {
        return [
            'users' => User::where('organization_id', auth()->user()->organization_id)
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Usuarios del Sistema</flux:heading>
            <flux:text class="text-gray-500">Administra los accesos del personal de tu empresa.</flux:text>
        </div>
        <flux:button variant="primary" icon="user-plus" wire:click="$set('showCreateModal', true)">
            Nuevo Usuario
        </flux:button>
    </div>

    <!-- Tabla de Usuarios -->
    <flux:card>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Rol</flux:table.column>
                <flux:table.column>Acciones</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="font-medium">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :variant="$user->role->value === 'admin' ? 'success' : 'default'">
                                {{ ucfirst($user->role->value) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->id !== auth()->id())
                                <flux:button variant="danger" size="sm" icon="trash"
                                    wire:click="confirmDelete({{ $user->id }})" />
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
        <div class="mt-4">{{ $users->links() }}</div>
    </flux:card>

    <!-- Modal Crear Usuario -->
    <flux:modal wire:model="showCreateModal" class="max-w-lg">
        <form wire:submit="create" class="space-y-4">
            <flux:heading size="lg">Registrar Nuevo Usuario</flux:heading>

            <flux:field>
                <flux:label>Nombre Completo</flux:label>
                <flux:input wire:model="name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Correo Electrónico</flux:label>
                <flux:input type="email" wire:model="email" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Contraseña</flux:label>
                <flux:input type="password" wire:model="password" />
                <flux:error name="password" />
            </flux:field>

            <flux:select wire:model="role" label="Rol de Acceso">
                <flux:select.option value="admin">Administrador</flux:select.option>
                <flux:select.option value="operator">Operador</flux:select.option>
                <flux:select.option value="viewer">Visor</flux:select.option>
            </flux:select>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('showCreateModal', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Eliminar Usuario -->
    <flux:modal wire:model="showDeleteModal" class="max-w-md">
        <form wire:submit="delete" class="space-y-4">
            <flux:heading size="lg">¿Eliminar Usuario?</flux:heading>
            <flux:text>Esta acción es irreversible y revocará el acceso inmediatamente.</flux:text>
            <flux:error name="delete" />
            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('showDeleteModal', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="danger">Sí, eliminar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

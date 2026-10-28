<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;
use App\Domain\Organization\Actions\UpdateOrganizationAction;

new #[Layout('layouts.app')] class extends Component {
    public string $name = '';
    public ?string $tax_id = '';
    public ?string $email = '';
    public ?string $phone = '';
    public ?string $address = '';

    public function mount()
    {
        $organization = auth()->user()->organization;
        $this->name = $organization->name;
        $this->tax_id = $organization->tax_id ?? '';
        $this->email = $organization->email ?? '';
        $this->phone = $organization->phone ?? '';
        $this->address = $organization->address ?? '';
    }

    public function save(UpdateOrganizationAction $action)
    {
        $organization = auth()->user()->organization;
        Gate::authorize('manage', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $action->handle($organization, $validated);

        session()->flash('status', 'Datos de la empresa actualizados correctamente.');
        $this->redirect('/organizacion/ajustes', navigate: true);
    }
};
?>

<div class="max-w-3xl mx-auto py-8 space-y-6">
    <flux:heading size="xl">Ajustes de la Empresa</flux:heading>
    <flux:text class="text-gray-500">Actualiza la información corporativa y fiscal de tu organización.</flux:text>

    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <flux:field class="md:col-span-2">
                    <flux:label>Nombre de la Organización / Razón Social</flux:label>
                    <flux:input wire:model="name" placeholder="Ej. Mi Empresa S.A.C." />
                    <flux:error name="name" />
                </flux:field>

                <!-- Identificador Fiscal (RUC / NIT) -->
                <flux:field>
                    <flux:label>Identificador Fiscal (RUC / NIT)</flux:label>
                    <flux:input wire:model="tax_id" placeholder="Ej. 20123456789" />
                    <flux:error name="tax_id" />
                </flux:field>

                <!-- Teléfono -->
                <flux:field>
                    <flux:label>Teléfono de Contacto</flux:label>
                    <flux:input wire:model="phone" placeholder="Ej. +51 987 654 321" />
                    <flux:error name="phone" />
                </flux:field>

                <!-- Correo Electrónico -->
                <flux:field class="md:col-span-2">
                    <flux:label>Correo Corporativo</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="contacto@empresa.com" />
                    <flux:error name="email" />
                </flux:field>

                <!-- Dirección -->
                <flux:field class="md:col-span-2">
                    <flux:label>Dirección Fiscal</flux:label>
                    <flux:input wire:model="address" placeholder="Av. Principal 123, Ciudad" />
                    <flux:error name="address" />
                </flux:field>
            </div>

            <div class="flex justify-end pt-4">
                <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
            </div>
        </form>
    </flux:card>
</div>

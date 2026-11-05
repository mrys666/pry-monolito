<?php

use App\Domain\Organization\Actions\UpdateOrganizationAction;
use App\Domain\Organization\Models\Organization;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $name = '';
    public ?string $tax_id = '';
    public ?string $email = '';
    public ?string $phone = '';
    public ?string $address = '';

    public function mount(): void
    {
        $organization = $this->currentOrganization();
        Gate::authorize('manage', $organization);

        $this->name = $organization->name;
        $this->tax_id = $organization->tax_id ?? '';
        $this->email = $organization->email ?? '';
        $this->phone = $organization->phone ?? '';
        $this->address = $organization->address ?? '';
    }

    public function save(UpdateOrganizationAction $action): void
    {
        $organization = $this->currentOrganization();
        Gate::authorize('manage', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $action->handle($organization, $validated);

        Flux::toast(variant: 'success', text: 'Datos de la empresa actualizados correctamente.');
        $this->redirect('/organizacion/ajustes', navigate: true);
    }

    private function currentOrganization(): Organization
    {
        return Organization::findOrFail(auth()->user()->organization_id);
    }
};
?>

<div class="mx-auto max-w-4xl space-y-6">
    <x-page-header eyebrow="Organización" title="Ajustes de la empresa"
        description="Actualiza la información corporativa y fiscal que identifica a tu organización."
        icon="building-office-2">
        <flux:button type="submit" form="organization-settings-form" variant="primary" icon="check"
            wire:loading.attr="disabled" wire:target="save">
            Guardar cambios
        </flux:button>
    </x-page-header>

    <flux:callout variant="secondary" icon="information-circle">
        Estos datos se utilizan para identificar a tu organización en reportes, documentos y comunicaciones.
    </flux:callout>

    <form id="organization-settings-form" wire:submit="save" class="space-y-6">
        <flux:card class="space-y-6 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <flux:icon.building-office-2 class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Identidad corporativa</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Nombre legal y documento fiscal de la empresa.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field class="md:col-span-2">
                    <flux:label>Nombre de la organización / Razón social</flux:label>
                    <flux:input wire:model="name" placeholder="Ej. Mi Empresa S.A.C." autocomplete="organization" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Identificador fiscal (RUC / NIT)</flux:label>
                    <flux:input wire:model="tax_id" placeholder="Ej. 20123456789" />
                    <flux:error name="tax_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono de contacto</flux:label>
                    <flux:input wire:model="phone" placeholder="Ej. +51 987 654 321" autocomplete="tel" />
                    <flux:error name="phone" />
                </flux:field>
            </div>
        </flux:card>

        <flux:card class="space-y-6 p-5 sm:p-6">
            <div class="flex items-start gap-3 border-b border-zinc-200/70 pb-5 dark:border-zinc-800">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400">
                    <flux:icon.map-pin class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">Contacto y ubicación</flux:heading>
                    <flux:text class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        Canales de contacto y dirección fiscal.
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field class="md:col-span-2">
                    <flux:label>Correo corporativo</flux:label>
                    <flux:input type="email" wire:model="email" placeholder="contacto@empresa.com"
                        autocomplete="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Dirección fiscal</flux:label>
                    <flux:input wire:model="address" placeholder="Av. Principal 123, Ciudad"
                        autocomplete="street-address" />
                    <flux:error name="address" />
                </flux:field>
            </div>
        </flux:card>

        <div class="flex flex-col-reverse items-stretch justify-end gap-2 sm:flex-row sm:items-center">
            <flux:button href="{{ route('organization.users') }}" variant="subtle" wire:navigate>
                Gestionar usuarios
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save">
                Guardar cambios
            </flux:button>
        </div>
    </form>
</div>

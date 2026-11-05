@php
    $user = auth()->user();
    $roleLabel = match ($user?->role) {
        \App\Domain\Access\Enums\UserRole::Admin => 'Administrador',
        \App\Domain\Access\Enums\UserRole::Operator => 'Operador',
        \App\Domain\Access\Enums\UserRole::Viewer => 'Visor',
        default => 'Sin rol',
    };
@endphp

<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile :name="$user?->name ?? ''" :initials="$user?->initials() ?? ''"
        icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar :name="$user?->name ?? ''" :initials="$user?->initials() ?? ''" />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ $user?->name }}</flux:heading>
                <flux:text class="truncate">{{ $user?->email }}</flux:text>
            </div>
            <flux:badge color="zinc" size="sm">{{ $roleLabel }}</flux:badge>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                Ajustes de cuenta
            </flux:menu.item>

            <flux:menu.item :href="route('appearance.edit')" icon="swatch" wire:navigate>
                Apariencia
            </flux:menu.item>
        </flux:menu.radio.group>

        <flux:menu.separator />

        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Tema claro</flux:menu.item>
        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Tema oscuro</flux:menu.item>
        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">Tema del sistema</flux:menu.item>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer" data-test="logout-button">
                Cerrar sesión
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>

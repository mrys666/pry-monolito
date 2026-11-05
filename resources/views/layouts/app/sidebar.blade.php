@php
    $user = auth()->user();
    $role = $user?->role;
    $isAdmin = $role === \App\Domain\Access\Enums\UserRole::Admin;
    $canManageInventory = in_array(
        $role,
        [\App\Domain\Access\Enums\UserRole::Admin, \App\Domain\Access\Enums\UserRole::Operator],
        true,
    );
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')

    <style>
        /* Fuerza al CSS Grid de Flux a colapsar la fila del header en pantallas grandes */
        @media (min-width: 1024px) {
            body:has(> [data-flux-main]) {
                grid-template-rows: 0px 1fr auto !important;
            }
        }
    </style>
</head>

<body class="min-h-dvh bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">

    <!-- Mobile header (Debe ir antes del sidebar para no romper el grid-area) -->
    <flux:header
        class="border-b border-zinc-200/80 bg-white/95 backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-900/95">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('dashboard') }}" wire:navigate class="ms-2" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="$user?->initials() ?? ''" icon-trailing="chevron-down" />

            <flux:menu>
                <div class="px-3 py-2 text-sm">
                    <div class="font-medium text-zinc-900 dark:text-white">{{ $user?->name }}</div>
                    <div class="text-zinc-500 dark:text-zinc-400">{{ $user?->email }}</div>
                </div>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    Ajustes de cuenta
                </flux:menu.item>

                <flux:menu.separator />

                <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Tema claro</flux:menu.item>
                <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Tema oscuro</flux:menu.item>
                <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">Tema del sistema
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer">
                        Cerrar sesión
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Sidebar Desktop -->
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200/80 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group heading="Operación" class="grid gap-1">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    Panel
                </flux:sidebar.item>

                <flux:sidebar.item icon="cube" :href="route('catalog.index')"
                    :current="request()->routeIs('catalog.index')" wire:navigate>
                    Productos
                </flux:sidebar.item>

                @if ($canManageInventory)
                    <flux:sidebar.item icon="arrow-path-rounded-square" :href="route('inventory.index')"
                        :current="request()->routeIs('inventory.*')" wire:navigate>
                        Inventario
                    </flux:sidebar.item>
                @endif

                <flux:sidebar.item icon="tag" :href="route('catalog.categories.index')"
                    :current="request()->routeIs('catalog.categories.*')" wire:navigate>
                    Categorías
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Análisis" class="mt-4 grid gap-1">
                <flux:sidebar.item icon="chart-bar" :href="route('reports.index')"
                    :current="request()->routeIs('reports.*')" wire:navigate>
                    Reportes
                </flux:sidebar.item>
            </flux:sidebar.group>

            @if ($isAdmin)
                <flux:sidebar.group heading="Organización" class="mt-4 grid gap-1">
                    <flux:sidebar.item icon="building-office-2" :href="route('organization.settings')"
                        :current="request()->routeIs('organization.settings')" wire:navigate>
                        Empresa
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="users" :href="route('organization.users')"
                        :current="request()->routeIs('organization.users')" wire:navigate>
                        Usuarios
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endif
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="hidden lg:block" :name="$user?->name ?? ''" />
    </flux:sidebar>

    <!-- Inyecta el contenido de app.blade.php (el <flux:main>) -->
    {{ $slot }}

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>

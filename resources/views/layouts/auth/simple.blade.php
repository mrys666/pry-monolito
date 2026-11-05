<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-svh bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-6 md:p-10">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-60 dark:opacity-30"
            style="background-image: radial-gradient(circle at 20% 10%, rgba(16, 185, 129, 0.12), transparent 30%), radial-gradient(circle at 80% 90%, rgba(245, 158, 11, 0.10), transparent 30%);">
        </div>

        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium">
                <span
                    class="flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900">
                    <x-app-logo-icon class="size-5 fill-current" />
                </span>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Inventario Pro') }}</span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Control de inventario para tu organización</span>
            </a>

            <div class="mt-4 flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>

        <p class="text-xs text-zinc-400 dark:text-zinc-500">
            © {{ now()->year }} {{ config('app.name', 'Inventario Pro') }} · Todos los derechos reservados.
        </p>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>

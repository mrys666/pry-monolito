<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inventario Pro') }} — Control de inventario para equipos</title>
    <meta name="description"
        content="Centraliza tu catálogo, registra entradas y salidas, y toma decisiones con reportes en tiempo real. Inventario Pro es el control de inventario que tu organización necesita.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite('resources/css/app.css')
    @fluxAppearance
</head>

<body class="min-h-svh bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <header
        class="sticky top-0 z-50 border-b border-zinc-200/70 bg-zinc-50/85 backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-950/85">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ config('app.name') }}">
                <span
                    class="flex size-9 items-center justify-center rounded-xl bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900">
                    <x-app-logo-icon class="size-5 fill-current" />
                </span>
                <span class="text-base font-semibold tracking-tight">{{ config('app.name', 'Inventario Pro') }}</span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navegación principal">
                <a href="#producto"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white">
                    Producto
                </a>
                <a href="#funciones"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white">
                    Funciones
                </a>
                <a href="#roles"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white">
                    Roles
                </a>
                <a href="#como-funciona"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-white">
                    Cómo funciona
                </a>
            </nav>

            <div class="flex items-center gap-2">
                <button type="button"
                    onclick="window.Flux.applyAppearance(document.documentElement.classList.contains('dark') ? 'light' : 'dark')"
                    class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                    aria-label="Cambiar entre tema claro y oscuro">
                    <flux:icon.sun class="hidden size-4 dark:block" />
                    <flux:icon.moon class="size-4 dark:hidden" />
                </button>

                <div class="hidden items-center gap-2 sm:flex">
                    @auth
                        <flux:button href="{{ route('dashboard') }}" variant="primary" icon="arrow-right">
                            Ir al panel
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="subtle">Iniciar sesión</flux:button>
                        <flux:button href="{{ route('register') }}" variant="primary" icon="arrow-right">
                            Comenzar gratis
                        </flux:button>
                    @endauth
                </div>

                <details class="relative sm:hidden">
                    <summary
                        class="flex size-9 cursor-pointer list-none items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 [&::-webkit-details-marker]:hidden"
                        aria-label="Abrir menú">
                        <flux:icon.bars-3 class="size-4" />
                    </summary>

                    <div
                        class="absolute end-0 z-50 mt-2 w-60 rounded-xl border border-zinc-200 bg-white p-2 shadow-xl dark:border-zinc-800 dark:bg-zinc-900">
                        <a href="#producto"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Producto</a>
                        <a href="#funciones"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Funciones</a>
                        <a href="#roles"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Roles</a>
                        <a href="#como-funciona"
                            class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Cómo funciona</a>
                        <div class="my-2 border-t border-zinc-200 dark:border-zinc-800"></div>
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="block rounded-lg px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-400/10">Ir
                                al panel</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Iniciar
                                sesión</a>
                            <a href="{{ route('register') }}"
                                class="block rounded-lg px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-400/10">Comenzar
                                gratis</a>
                        @endauth
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero -->
        <section id="producto" class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,rgba(113,113,122,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(113,113,122,0.08)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]">
                </div>
                <div class="absolute -left-24 top-16 size-72 rounded-full bg-emerald-400/20 blur-3xl dark:bg-emerald-500/10"></div>
                <div class="absolute -right-24 top-40 size-72 rounded-full bg-amber-400/20 blur-3xl dark:bg-amber-500/10"></div>
            </div>

            <div class="mx-auto grid max-w-7xl gap-12 px-4 pb-20 pt-14 sm:px-6 sm:pt-20 lg:grid-cols-2 lg:items-center lg:gap-10 lg:px-8 lg:pb-28">
                <div class="max-w-xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <span class="relative flex size-1.5">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span>
                        </span>
                        Inventario en tiempo real, sin hojas de cálculo
                    </div>

                    <h1 class="mt-6 text-4xl font-semibold tracking-tight text-zinc-900 sm:text-5xl lg:text-6xl dark:text-white">
                        Tu inventario,
                        <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent dark:from-emerald-400 dark:to-teal-300">
                            bajo control.
                        </span>
                    </h1>

                    <p class="mt-6 text-lg leading-relaxed text-zinc-600 dark:text-zinc-300">
                        Centraliza productos, registra entradas y salidas, y toma decisiones con reportes claros.
                        Diseñado para equipos que necesitan saber qué tienen, dónde está y cuándo reponer.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" icon="arrow-right" size="base">
                                Ir al panel
                            </flux:button>
                        @else
                            <flux:button href="{{ route('register') }}" variant="primary" icon="arrow-right" size="base">
                                Comenzar gratis
                            </flux:button>
                            <flux:button href="{{ route('login') }}" variant="outline" icon="arrow-right-start-on-rectangle"
                                size="base">
                                Iniciar sesión
                            </flux:button>
                        @endauth
                    </div>

                    <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                        Configuración en minutos · Catálogo, movimientos y reportes incluidos.
                    </p>

                    <dl class="mt-10 grid grid-cols-2 gap-4 border-t border-zinc-200/80 pt-6 sm:grid-cols-4 dark:border-zinc-800">
                        <div>
                            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Trazabilidad</dt>
                            <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">100% de movimientos</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Roles</dt>
                            <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">Admin · Operador · Visor</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Organización</dt>
                            <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">Multiempresa</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Actualización</dt>
                            <dd class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">Tiempo real</dd>
                        </div>
                    </dl>
                </div>

                <!-- Dashboard preview -->
                <div class="relative mx-auto w-full max-w-xl lg:max-w-none">
                    <div class="absolute -inset-4 rounded-[2.5rem] bg-gradient-to-tr from-emerald-500/20 via-transparent to-amber-500/20 blur-2xl"></div>

                    <div class="relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-2xl shadow-zinc-900/10 dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-black/40">
                        <div class="flex items-center gap-2 border-b border-zinc-200/70 px-4 py-3 dark:border-zinc-800">
                            <span class="size-2.5 rounded-full bg-red-400"></span>
                            <span class="size-2.5 rounded-full bg-amber-400"></span>
                            <span class="size-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ms-3 flex-1 truncate text-xs font-medium text-zinc-400">
                                app.inventariopro.com/panel
                            </span>
                            <span class="hidden items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-1 text-[10px] font-medium text-emerald-600 sm:inline-flex dark:text-emerald-400">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                En vivo
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-[132px_1fr]">
                            <aside class="hidden border-e border-zinc-200/70 bg-zinc-50/70 p-3 sm:block dark:border-zinc-800 dark:bg-zinc-950/30">
                                <div class="flex items-center gap-2 px-1 pb-3">
                                    <span class="flex size-6 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                                        <x-app-logo-icon class="size-3.5 fill-current" />
                                    </span>
                                    <span class="text-[11px] font-semibold">Inventario</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="flex items-center gap-2 rounded-md bg-white px-2 py-1.5 text-[10px] font-medium text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-white">
                                        <flux:icon.squares-2x2 class="size-3" />
                                        Panel
                                    </span>
                                    <span class="flex items-center gap-2 rounded-md px-2 py-1.5 text-[10px] text-zinc-500 dark:text-zinc-400">
                                        <flux:icon.cube class="size-3" />
                                        Productos
                                    </span>
                                    <span class="flex items-center gap-2 rounded-md px-2 py-1.5 text-[10px] text-zinc-500 dark:text-zinc-400">
                                        <flux:icon.arrow-path-rounded-square class="size-3" />
                                        Inventario
                                    </span>
                                    <span class="flex items-center gap-2 rounded-md px-2 py-1.5 text-[10px] text-zinc-500 dark:text-zinc-400">
                                        <flux:icon.chart-bar class="size-3" />
                                        Reportes
                                    </span>
                                </div>
                            </aside>

                            <div class="space-y-4 p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-medium text-zinc-400">Panel de control</p>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Hola, Ana 👋</p>
                                    </div>
                                    <span class="rounded-md bg-zinc-900 px-2 py-1 text-[10px] font-medium text-white dark:bg-white dark:text-zinc-900">
                                        Este mes
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-lg border border-zinc-200/70 bg-white p-2.5 dark:border-zinc-800 dark:bg-zinc-950/30">
                                        <p class="text-[9px] text-zinc-400">Productos</p>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">124</p>
                                    </div>
                                    <div class="rounded-lg border border-zinc-200/70 bg-white p-2.5 dark:border-zinc-800 dark:bg-zinc-950/30">
                                        <p class="text-[9px] text-zinc-400">Existencias</p>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">1,840</p>
                                    </div>
                                    <div class="rounded-lg border border-amber-200/70 bg-amber-50/70 p-2.5 dark:border-amber-400/20 dark:bg-amber-400/10">
                                        <p class="text-[9px] text-amber-600 dark:text-amber-400">Alertas</p>
                                        <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">3</p>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-zinc-200/70 dark:border-zinc-800">
                                    <div class="flex items-center justify-between border-b border-zinc-200/70 px-3 py-2 dark:border-zinc-800">
                                        <p class="text-[10px] font-semibold text-zinc-700 dark:text-zinc-200">Stock crítico</p>
                                        <span class="text-[9px] text-zinc-400">Ver todo</span>
                                    </div>
                                    <div class="divide-y divide-zinc-200/70 dark:divide-zinc-800">
                                        @php
                                            $demoProducts = [
                                                ['name' => 'Teclado mecánico', 'sku' => 'TEC-001', 'stock' => 8, 'min' => 12, 'width' => 40, 'tone' => 'amber'],
                                                ['name' => 'Monitor 24"', 'sku' => 'MON-014', 'stock' => 3, 'min' => 5, 'width' => 25, 'tone' => 'red'],
                                                ['name' => 'Mouse inalámbrico', 'sku' => 'MOU-008', 'stock' => 24, 'min' => 10, 'width' => 75, 'tone' => 'emerald'],
                                            ];
                                        @endphp
                                        @foreach ($demoProducts as $demo)
                                            <div class="flex items-center gap-2 px-3 py-2">
                                                <span class="flex size-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                                    <flux:icon.cube class="size-3" />
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-[10px] font-medium text-zinc-800 dark:text-zinc-200">{{ $demo['name'] }}</p>
                                                    <div class="mt-1 h-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                        <div class="h-full rounded-full {{ $demo['tone'] === 'red' ? 'bg-red-500' : ($demo['tone'] === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                            style="width: {{ $demo['width'] }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="shrink-0 text-[9px] font-semibold {{ $demo['tone'] === 'red' ? 'text-red-600 dark:text-red-400' : ($demo['tone'] === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                                    {{ $demo['stock'] }} / {{ $demo['min'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center justify-between rounded-lg bg-zinc-900 px-3 py-2 text-white dark:bg-white dark:text-zinc-900">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.bolt class="size-3.5" />
                                        <span class="text-[10px] font-medium">Sincronización activa</span>
                                    </div>
                                    <span class="text-[9px] opacity-70">Reverb</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-4 -left-4 hidden items-center gap-2 rounded-xl border border-zinc-200/80 bg-white px-3 py-2 shadow-lg sm:flex dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <flux:icon.check-circle class="size-4" />
                        </span>
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-900 dark:text-white">Entrada registrada</p>
                            <p class="text-[9px] text-zinc-400">+50 · Teclado mecánico</p>
                        </div>
                    </div>

                    <div class="absolute -right-3 top-16 hidden items-center gap-2 rounded-xl border border-amber-200/80 bg-white px-3 py-2 shadow-lg sm:flex dark:border-amber-400/20 dark:bg-zinc-900">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <flux:icon.exclamation-triangle class="size-4" />
                        </span>
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-900 dark:text-white">Stock bajo</p>
                            <p class="text-[9px] text-zinc-400">Monitor 24" · 3 un.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Capabilities strip -->
        <section class="border-y border-zinc-200/70 bg-white/60 dark:border-zinc-800 dark:bg-zinc-900/40">
            <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-8 sm:px-6 md:grid-cols-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <flux:icon.bolt class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Tiempo real</p>
                        <p class="text-xs text-zinc-500">Eventos con Reverb</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        <flux:icon.building-office-2 class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Multiempresa</p>
                        <p class="text-xs text-zinc-500">Datos aislados por organización</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                        <flux:icon.shield-check class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Auditoría</p>
                        <p class="text-xs text-zinc-500">Cada movimiento queda registrado</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <flux:icon.users class="size-4" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Roles claros</p>
                        <p class="text-xs text-zinc-500">Permisos por responsabilidad</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="funciones" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Todo en un solo lugar</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                    Menos planillas. Más control.
                </h2>
                <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-300">
                    Las herramientas esenciales para que tu equipo opere con claridad y tu inventario no pierda el ritmo.
                </p>
            </div>

            @php
                $features = [
                    [
                        'icon' => 'cube',
                        'color' => 'sky',
                        'title' => 'Catálogo centralizado',
                        'text' => 'Productos, SKU, categorías, precios y niveles mínimos en un solo lugar, siempre consistentes.',
                    ],
                    [
                        'icon' => 'arrow-path-rounded-square',
                        'color' => 'emerald',
                        'title' => 'Entradas y salidas',
                        'text' => 'Registra cada movimiento con cantidad, motivo y responsable. El stock se actualiza al instante.',
                    ],
                    [
                        'icon' => 'chart-bar',
                        'color' => 'violet',
                        'title' => 'Reportes accionables',
                        'text' => 'Visualiza existencias, valorización, flujo del periodo y productos que requieren atención.',
                    ],
                    [
                        'icon' => 'exclamation-triangle',
                        'color' => 'amber',
                        'title' => 'Alertas de stock',
                        'text' => 'Detecta productos en o por debajo del mínimo y prioriza el reabastecimiento sin adivinar.',
                    ],
                    [
                        'icon' => 'users',
                        'color' => 'indigo',
                        'title' => 'Permisos por rol',
                        'text' => 'Administrador, operador y visor. Cada persona ve y hace solo lo que le corresponde.',
                    ],
                    [
                        'icon' => 'building-office-2',
                        'color' => 'rose',
                        'title' => 'Aislamiento por organización',
                        'text' => 'Cada empresa trabaja con sus propios productos, usuarios y movimientos. Sin cruces de datos.',
                    ],
                ];

                $featureClasses = [
                    'sky' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400',
                    'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
                    'violet' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400',
                    'amber' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400',
                    'indigo' => 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400',
                    'rose' => 'bg-rose-500/10 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400',
                ];
            @endphp

            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <div class="group rounded-2xl border border-zinc-200/80 bg-white p-6 transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/5 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-emerald-400/30">
                        <span class="flex size-11 items-center justify-center rounded-xl {{ $featureClasses[$feature['color']] }}">
                            <flux:icon :icon="$feature['icon']" variant="outline" class="size-5" />
                        </span>
                        <h3 class="mt-5 text-base font-semibold text-zinc-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Roles -->
        <section id="roles" class="border-y border-zinc-200/70 bg-white/60 dark:border-zinc-800 dark:bg-zinc-900/40">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Equipos alineados</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        Cada rol, con el acceso correcto.
                    </h2>
                    <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-300">
                        Define responsabilidades claras sin compartir contraseñas ni depender de una sola persona.
                    </p>
                </div>

                <div class="mt-14 grid gap-4 md:grid-cols-3">
                    <div class="relative rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-400/30 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <flux:icon.shield-check class="size-5" />
                            </span>
                            <flux:badge color="emerald" size="sm">Acceso total</flux:badge>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Administrador</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Para quien lidera la operación.</p>
                        <ul class="mt-5 space-y-2.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-emerald-500" />Gestiona catálogo y categorías.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-emerald-500" />Registra entradas, salidas y ajustes.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-emerald-500" />Administra usuarios y empresa.</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                <flux:icon.user class="size-5" />
                            </span>
                            <flux:badge color="sky" size="sm">Operación diaria</flux:badge>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Operador</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Para el día a día del almacén.</p>
                        <ul class="mt-5 space-y-2.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-sky-500" />Consulta el catálogo completo.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-sky-500" />Registra entradas y salidas.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-sky-500" />Accede a reportes operativos.</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-zinc-500/10 text-zinc-600 dark:text-zinc-300">
                                <flux:icon.eye class="size-5" />
                            </span>
                            <flux:badge color="zinc" size="sm">Solo consulta</flux:badge>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">Visor</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Para auditoría y supervisión.</p>
                        <ul class="mt-5 space-y-2.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-zinc-400" />Consulta productos y categorías.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-zinc-400" />Revisa reportes e indicadores.</li>
                            <li class="flex items-start gap-2"><flux:icon.check class="mt-0.5 size-4 shrink-0 text-zinc-400" />Sin permisos de edición.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="como-funciona" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="max-w-xl">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Empieza en minutos</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        De la planilla al control total en tres pasos.
                    </h2>
                    <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-300">
                        No necesitas una implementación compleja. Crea tu espacio, carga tus productos y empieza a
                        registrar movimientos con tu equipo.
                    </p>

                    <div class="mt-8 space-y-5">
                        <div class="flex gap-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">1</span>
                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Crea tu organización</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Registra tu empresa y define el nombre con el que operará tu equipo.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">2</span>
                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Carga tu catálogo</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Agrega productos, categorías, precios y niveles mínimos de stock.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">3</span>
                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Opera y decide</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Registra movimientos, revisa reportes y repón a tiempo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-emerald-500/10 via-transparent to-amber-500/10 blur-2xl"></div>
                    <div class="relative rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-xl shadow-zinc-900/5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium text-zinc-400">Reporte del periodo</p>
                                <p class="text-lg font-semibold text-zinc-900 dark:text-white">Flujo de inventario</p>
                            </div>
                            <flux:badge color="emerald" size="sm">Últimos 30 días</flux:badge>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs">
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Entradas</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">+640 un.</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full w-[82%] rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs">
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Salidas</span>
                                    <span class="font-semibold text-red-600 dark:text-red-400">-420 un.</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full w-[54%] rounded-full bg-red-500"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs">
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Ajustes</span>
                                    <span class="font-semibold text-amber-600 dark:text-amber-400">12</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full w-[18%] rounded-full bg-amber-500"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3 border-t border-zinc-200/70 pt-5 dark:border-zinc-800">
                            <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-950/40">
                                <p class="text-xs text-zinc-500">Valorización</p>
                                <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">S/ 48,920</p>
                            </div>
                            <div class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-950/40">
                                <p class="text-xs text-zinc-500">Salud del stock</p>
                                <p class="mt-1 text-lg font-semibold text-emerald-600 dark:text-emerald-400">94%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8 lg:pb-24">
            <div class="relative overflow-hidden rounded-3xl bg-zinc-900 px-6 py-14 text-center shadow-2xl shadow-zinc-900/20 sm:px-12 lg:px-16 dark:bg-zinc-900 dark:ring-1 dark:ring-zinc-800">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-20 -top-20 size-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-24 -right-16 size-64 rounded-full bg-amber-500/20 blur-3xl"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]"></div>
                </div>

                <div class="relative mx-auto max-w-2xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Toma el control de tu inventario hoy.
                    </h2>
                    <p class="mt-4 text-lg text-zinc-300">
                        Centraliza tu operación, da visibilidad a tu equipo y evita quiebres de stock con información
                        confiable.
                    </p>

                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" icon="arrow-right" size="base">
                                Ir al panel
                            </flux:button>
                        @else
                            <flux:button href="{{ route('register') }}" variant="primary" icon="arrow-right" size="base">
                                Crear mi cuenta
                            </flux:button>
                            <flux:button href="{{ route('login') }}" variant="outline" size="base">
                                Ya tengo cuenta
                            </flux:button>
                        @endauth
                    </div>

                    <p class="mt-5 text-sm text-zinc-400">
                        Catálogo, movimientos, reportes y roles incluidos.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-200/70 bg-white/60 dark:border-zinc-800 dark:bg-zinc-900/40">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
                <div class="max-w-sm">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                            <x-app-logo-icon class="size-4 fill-current" />
                        </span>
                        <span class="text-sm font-semibold tracking-tight">{{ config('app.name', 'Inventario Pro') }}</span>
                    </a>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                        Control de inventario para organizaciones que necesitan claridad, trazabilidad y decisiones
                        oportunas.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Producto</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li><a href="#funciones" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Funciones</a></li>
                            <li><a href="#roles" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Roles</a></li>
                            <li><a href="#como-funciona" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Cómo funciona</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Acceso</p>
                        <ul class="mt-3 space-y-2 text-sm">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Panel</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Iniciar sesión</a></li>
                                <li><a href="{{ route('register') }}" class="text-zinc-600 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400">Crear cuenta</a></li>
                            @endauth
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Tecnología</p>
                        <ul class="mt-3 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <li>Laravel + Livewire</li>
                            <li>Flux UI + Tailwind</li>
                            <li>Reverb en tiempo real</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-zinc-200/70 pt-6 text-xs text-zinc-400 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                <p>© {{ now()->year }} {{ config('app.name', 'Inventario Pro') }}. Todos los derechos reservados.</p>
                <p>Hecho para equipos que cuidan cada unidad.</p>
            </div>
        </div>
    </footer>
</body>

</html>

@props([
    'label',
    'value',
    'description' => null,
    'icon' => 'chart-bar',
    'color' => 'zinc',
])

@php
    $iconClasses = match ($color) {
        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
        'sky' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400',
        'amber' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400',
        'red' => 'bg-red-500/10 text-red-600 dark:bg-red-400/10 dark:text-red-400',
        'violet' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400',
        default => 'bg-zinc-500/10 text-zinc-600 dark:bg-zinc-400/10 dark:text-zinc-300',
    };
@endphp

<flux:card
    class="group relative overflow-hidden p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md hover:shadow-zinc-900/5 dark:hover:shadow-black/20">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 space-y-1">
            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</flux:text>
            <p class="truncate text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $value }}</p>
            @if ($description)
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">{{ $description }}</flux:text>
            @endif
        </div>

        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}">
            <flux:icon :icon="$icon" variant="outline" class="size-5" />
        </span>
    </div>

    @if (! $slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</flux:card>

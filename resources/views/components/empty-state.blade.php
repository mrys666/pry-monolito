@props([
    'icon' => 'archive-box',
    'title',
    'description' => null,
    'color' => 'zinc',
])

@php
    $iconClasses = match ($color) {
        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
        'amber' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400',
        'red' => 'bg-red-500/10 text-red-600 dark:bg-red-400/10 dark:text-red-400',
        default => 'bg-zinc-500/10 text-zinc-500 dark:bg-zinc-400/10 dark:text-zinc-400',
    };
@endphp

<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <span class="mb-4 flex size-12 items-center justify-center rounded-2xl {{ $iconClasses }}">
        <flux:icon :icon="$icon" variant="outline" class="size-6" />
    </span>

    <flux:heading size="sm">{{ $title }}</flux:heading>

    @if ($description)
        <flux:text class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>

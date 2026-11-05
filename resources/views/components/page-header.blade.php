@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'icon' => null,
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if ($eyebrow)
            <div class="mb-1 flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                @if ($icon)
                    <flux:icon :icon="$icon" class="size-4" />
                @endif
                {{ $eyebrow }}
            </div>
        @endif

        <flux:heading size="xl" level="1">{{ $title }}</flux:heading>

        @if ($description)
            <flux:text class="mt-1 max-w-2xl text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

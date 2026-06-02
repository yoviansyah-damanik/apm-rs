@props(['key', 'label', 'description', 'icon', 'result' => null])

@php
    $statusColor = match($result['status'] ?? null) {
        'success' => 'border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20',
        'error'   => 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20',
        default   => 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800',
    };
    $iconColor = match($result['status'] ?? null) {
        'success' => 'text-primary-600 dark:text-primary-400',
        'error'   => 'text-red-500 dark:text-red-400',
        default   => 'text-zinc-400',
    };
@endphp

<div
    {{ $attributes }}
    wire:loading.class="opacity-60 pointer-events-none"
    class="relative rounded-xl border p-5 cursor-pointer transition-all hover:shadow-md {{ $statusColor }}"
>
    <div wire:loading wire:target="{{ $attributes->wire('click')->value() }}"
         class="absolute inset-0 flex items-center justify-center rounded-xl bg-white/60 dark:bg-zinc-800/60 z-10">
        <flux:icon.arrow-path class="size-6 animate-spin text-zinc-500" />
    </div>

    <div class="flex items-start gap-4">
        <div class="shrink-0 mt-0.5">
            <flux:icon :name="$icon" class="size-7 {{ $iconColor }}" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <flux:heading size="sm">{{ $label }}</flux:heading>
                @if ($result)
                    @if ($result['status'] === 'success')
                        <flux:badge size="sm" color="green">OK</flux:badge>
                    @else
                        <flux:badge size="sm" color="red">Gagal</flux:badge>
                    @endif
                @endif
            </div>
            <flux:text class="text-zinc-500 text-xs mt-0.5">{{ $description }}</flux:text>

            @if ($result)
                <p class="mt-2 text-sm {{ $result['status'] === 'success' ? 'text-primary-700 dark:text-primary-300' : 'text-red-600 dark:text-red-400' }}">
                    {{ $result['message'] }}
                </p>
                <p class="text-xs text-zinc-400 mt-1">Diuji pukul {{ $result['time'] }}</p>
            @else
                <p class="mt-2 text-xs text-zinc-400 italic">Belum diuji — klik untuk menguji</p>
            @endif
        </div>
    </div>
</div>

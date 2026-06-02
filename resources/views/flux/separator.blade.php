@props([
    'orientation' => null,
    'vertical' => false,
    'variant' => null,
    'faint' => false,
    'text' => null,
    'textColor' => null,
])

@php
    $orientation ??= $vertical ? 'vertical' : 'horizontal';

    $classes = Flux::classes('border-0 print:border-2 print:border-black [print-color-adjust:exact]')
        ->add(
            match ($variant) {
                'subtle' => 'bg-zinc-800/5 dark:bg-white/10',
                'white' => 'bg-white',
                default => 'bg-zinc-800/15 dark:bg-white/20',
            },
        )
        ->add(
            match ($orientation) {
                'horizontal' => 'h-px w-full',
                'vertical' => 'self-stretch self-center w-px',
            },
        );

    $textClasses = Flux::classes('shrink mx-6 font-medium text-sm whitespace-nowrap')->add(
        match ($textColor) {
            'white' => 'text-white',
            'black' => 'text-black',
            'primary' => 'text-primary-600 dark:text-primary-400',
            'secondary' => 'text-secondary-600 dark:text-secondary-400',
            default => 'text-zinc-500 dark:text-zinc-300',
        },
    );
@endphp

<?php if ($text): ?>
<div data-orientation="{{ $orientation }}" class="flex items-center w-full" role="none" data-flux-separator>
    <div {{ $attributes->class([$classes, 'grow']) }}></div>

    <span {{ $attributes->class([$textClasses]) }}>{{ $text }}</span>

    <div {{ $attributes->class([$classes, 'grow']) }}></div>
</div>
<?php else: ?>
<div data-orientation="{{ $orientation }}" role="none" {{ $attributes->class($classes, 'shrink-0') }}
    data-flux-separator></div>
<?php endif; ?>

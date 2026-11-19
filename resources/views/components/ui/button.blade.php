@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 border-transparent',
        'secondary' => 'bg-secondary-600 text-white hover:bg-secondary-700 border-transparent',
        'outline' => 'bg-surface text-[color:var(--sme-text)] border-sme hover:bg-canvas',
        'ghost' => 'bg-transparent text-[color:var(--sme-text)] border-transparent hover:bg-canvas',
        'danger' => 'bg-danger-600 text-white hover:bg-danger-700 border-transparent',
        'success' => 'bg-success-600 text-white hover:bg-success-700 border-transparent',
    ];
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-lg border font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    {{ $slot }}
</button>

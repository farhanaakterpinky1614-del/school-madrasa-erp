@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'sme-btn-primary border-transparent',
        'secondary' => 'sme-btn-secondary border-transparent',
        'outline' => 'bg-surface/90 text-[color:var(--sme-text)] border-sme shadow-sme-soft hover:border-primary-300/50 hover:bg-white dark:hover:bg-slate-900',
        'ghost' => 'bg-transparent text-[color:var(--sme-text)] border-transparent hover:bg-primary-50/70 dark:hover:bg-white/5',
        'danger' => 'bg-gradient-to-br from-danger-600 to-rose-500 text-white border-transparent shadow-[0_8px_18px_rgba(225,29,72,0.25)] hover:from-danger-700 hover:to-rose-600',
        'success' => 'bg-gradient-to-br from-success-700 to-emerald-500 text-white border-transparent shadow-[0_8px_18px_rgba(5,150,105,0.25)] hover:from-success-800 hover:to-emerald-600',
    ];
    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs rounded-lg',
        'md' => 'px-3.5 py-2 text-sm rounded-xl',
        'lg' => 'px-4 py-2.5 text-sm rounded-xl',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 border font-medium transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    {{ $slot }}
</button>

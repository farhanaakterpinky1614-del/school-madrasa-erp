@props([
    'tone' => 'slate',
])

@php
    $tones = [
        'slate' => 'bg-slate-100/90 text-slate-700 ring-1 ring-slate-200/80 dark:bg-slate-800/80 dark:text-slate-200 dark:ring-slate-700',
        'success' => 'bg-gradient-to-r from-success-50 to-emerald-50 text-success-700 ring-1 ring-success-200/70 dark:from-success-700/20 dark:to-emerald-900/10 dark:text-success-300 dark:ring-success-700/40',
        'warning' => 'bg-gradient-to-r from-warning-50 to-amber-50 text-warning-700 ring-1 ring-warning-200/70 dark:from-warning-700/20 dark:to-amber-900/10 dark:text-warning-300 dark:ring-warning-700/40',
        'danger' => 'bg-gradient-to-r from-danger-50 to-rose-50 text-danger-700 ring-1 ring-danger-200/70 dark:from-danger-700/20 dark:to-rose-900/10 dark:text-danger-300 dark:ring-danger-700/40',
        'primary' => 'bg-gradient-to-r from-primary-50 to-secondary-50 text-primary-700 ring-1 ring-primary-200/70 dark:from-primary-700/25 dark:to-secondary-900/20 dark:text-primary-300 dark:ring-primary-700/40',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-semibold', $tones[$tone] ?? $tones['slate']]) }}>
    {{ $slot }}
</span>

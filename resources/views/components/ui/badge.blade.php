@props([
    'tone' => 'slate',
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'success' => 'bg-success-50 text-success-700 dark:bg-success-700/20 dark:text-success-300',
        'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-700/20 dark:text-warning-300',
        'danger' => 'bg-danger-50 text-danger-700 dark:bg-danger-700/20 dark:text-danger-300',
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-700/20 dark:text-primary-300',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium', $tones[$tone] ?? $tones['slate']]) }}>
    {{ $slot }}
</span>

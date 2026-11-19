@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'input-'.uniqid();
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-[color:var(--sme-text)]">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $id }}"
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm text-[color:var(--sme-text)] placeholder:text-muted shadow-sme-soft transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 disabled:opacity-50 '.($error ? 'border-danger-500 focus:ring-danger-500/20' : ''),
        ]) }}
    />

    @if ($error)
        <p class="text-xs text-danger-600 dark:text-danger-400">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
</div>

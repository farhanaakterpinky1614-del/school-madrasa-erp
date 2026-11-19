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
            'class' => 'block w-full rounded-lg border border-sme bg-surface px-3 py-2 text-sm text-[color:var(--sme-text)] placeholder:text-muted shadow-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-50 '.($error ? 'border-danger-500' : ''),
        ]) }}
    />

    @if ($error)
        <p class="text-xs text-danger-600 dark:text-danger-400">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
</div>

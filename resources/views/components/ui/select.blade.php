@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'select-'.uniqid();
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border border-sme bg-surface px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 '.($error ? 'border-danger-500' : ''),
        ]) }}
    >
        {{ $slot }}
    </select>

    @if ($error)
        <p class="text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
</div>

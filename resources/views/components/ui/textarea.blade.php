@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'textarea-'.uniqid();
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium">{{ $label }}</label>
    @endif

    <textarea
        id="{{ $id }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm shadow-sme-soft transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 '.($error ? 'border-danger-500' : ''),
            'rows' => $attributes->get('rows', 3),
        ]) }}
    >{{ $slot }}</textarea>

    @if ($error)
        <p class="text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
</div>

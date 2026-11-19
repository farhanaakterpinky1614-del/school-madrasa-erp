@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['overflow-hidden rounded-2xl sme-surface']) }}>
    @if ($title || $description)
        <header class="sme-panel-header px-5 py-4">
            @if ($title)
                <h2 class="text-base font-semibold tracking-tight">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-muted">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="px-5 py-5">
        {{ $slot }}
    </div>
</section>

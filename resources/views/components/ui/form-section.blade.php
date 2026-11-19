@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['rounded-lg border border-sme bg-surface']) }}>
    @if ($title || $description)
        <header class="border-b border-sme px-5 py-4">
            @if ($title)
                <h2 class="text-base font-semibold">{{ $title }}</h2>
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

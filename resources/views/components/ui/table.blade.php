@props([
    'headers' => [],
])

<div {{ $attributes->class(['overflow-hidden rounded-lg border border-sme bg-surface']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[color:var(--sme-border)] text-sm">
            @if (count($headers))
                <thead class="bg-canvas/80">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="sticky top-0 px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-muted">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-[color:var(--sme-border)]">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

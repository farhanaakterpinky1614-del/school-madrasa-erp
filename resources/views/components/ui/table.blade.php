@props([
    'headers' => [],
])

<div {{ $attributes->class(['overflow-hidden rounded-2xl sme-surface']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[color:var(--sme-border)] text-sm">
            @if (count($headers))
                <thead class="sme-panel-header">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="sticky top-0 px-4 py-3.5 text-start text-xs font-semibold uppercase tracking-[0.08em] text-muted">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-[color:var(--sme-border)] bg-[color:var(--sme-surface-solid)]/40">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

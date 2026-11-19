@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => 'lg',
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    <template x-teleport="body">
        <div
            x-show="open"
            class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
            style="display: none;"
            role="dialog"
            aria-modal="true"
            @if ($title) aria-labelledby="modal-title-{{ $name }}" @endif
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]"
                @click="open = false"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
                class="relative w-full {{ $widths[$maxWidth] ?? $widths['lg'] }} overflow-hidden rounded-2xl sme-surface-solid shadow-sme"
            >
                <div class="sme-topbar-accent"></div>
                <div class="sme-panel-header flex items-start justify-between px-5 py-4">
                    <div>
                        @if ($title)
                            <h2 id="modal-title-{{ $name }}" class="text-base font-semibold tracking-tight">{{ $title }}</h2>
                        @endif
                        @isset($subtitle)
                            <p class="mt-0.5 text-sm text-muted">{{ $subtitle }}</p>
                        @endisset
                    </div>
                    <button type="button" class="rounded-lg p-1 text-muted hover:bg-canvas" @click="open = false" aria-label="{{ __('ui.close') }}">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <div class="px-5 py-4">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="flex justify-end gap-2 border-t border-sme bg-gradient-to-b from-transparent to-slate-50/80 px-5 py-4 dark:to-slate-900/40">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>

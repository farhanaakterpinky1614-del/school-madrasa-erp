@php
    use App\Core\Support\Locale;
    $locales = Locale::all();
@endphp

<header class="sticky top-0 z-20 border-b border-sme bg-[color:var(--sme-topbar)]/90 backdrop-blur">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg border border-sme bg-surface p-2 text-muted lg:hidden"
            @click="$store.sidebar.toggleMobile()"
            aria-label="{{ __('ui.open_menu') }}"
        >
            <x-lucide-menu class="h-5 w-5" />
        </button>

        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium">{{ __('ui.demo_tenant') }}</p>
            <p class="truncate text-xs text-muted">{{ __('ui.phase_01_shell') }}</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Locale switcher --}}
            <div class="relative" x-data="{ open: false }">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-sme bg-surface px-2.5 py-2 text-xs font-medium text-[color:var(--sme-text)]"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                    :aria-expanded="open"
                    aria-haspopup="listbox"
                    aria-label="{{ __('ui.language') }}"
                >
                    <x-lucide-languages class="h-4 w-4 text-muted" />
                    <span class="hidden sm:inline">{{ $locales[app()->getLocale()]['native'] ?? strtoupper(app()->getLocale()) }}</span>
                </button>
                <div
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    class="absolute end-0 z-30 mt-2 w-44 overflow-hidden rounded-lg border border-sme bg-surface shadow-sme"
                    style="display: none;"
                    role="listbox"
                >
                    @foreach ($locales as $code => $meta)
                        <a
                            href="{{ route('locale.update', $code) }}"
                            class="flex items-center justify-between px-3 py-2 text-sm hover:bg-canvas {{ app()->getLocale() === $code ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300' : '' }}"
                            role="option"
                            aria-selected="{{ app()->getLocale() === $code ? 'true' : 'false' }}"
                        >
                            <span>{{ $meta['native'] }}</span>
                            <span class="text-xs text-muted uppercase">{{ $code }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Theme toggle --}}
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-sme bg-surface p-2 text-muted hover:text-[color:var(--sme-text)]"
                @click="$store.theme.toggle()"
                aria-label="{{ __('ui.toggle_theme') }}"
            >
                <x-lucide-sun class="h-4 w-4 dark:hidden" />
                <x-lucide-moon class="hidden h-4 w-4 dark:block" />
            </button>

            <div class="hidden items-center gap-2 rounded-lg border border-sme bg-surface px-2.5 py-1.5 sm:flex">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">AD</span>
                <div class="leading-tight">
                    <p class="text-xs font-medium">{{ __('ui.demo_user') }}</p>
                    <p class="text-[11px] text-muted">{{ __('ui.demo_role') }}</p>
                </div>
            </div>
        </div>
    </div>
</header>

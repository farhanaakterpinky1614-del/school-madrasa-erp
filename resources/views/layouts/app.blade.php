@php
    use App\Core\Support\Locale;
    $dir = Locale::direction();
    $locale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.app_name')) — {{ config('app.name') }}</title>
    <script>
        (function () {
            const theme = localStorage.getItem('sme-theme') || 'light';
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    class="h-full text-[color:var(--sme-text)]"
    x-data
    x-init="$store.theme.init()"
>
    <div class="min-h-full lg:flex">
        @include('layouts.partials.sidebar')

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col transition-sidebar"
             :class="$store.sidebar.collapsed ? 'lg:ms-20' : 'lg:ms-64'">
            @include('layouts.partials.topbar')

            <main class="relative flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @hasSection('page-header')
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        @yield('page-header')
                    </div>
                @endif

                @if (session('status'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mb-4 flex items-center justify-between rounded-xl border border-success-200/70 bg-gradient-to-r from-success-50 to-emerald-50/40 px-4 py-3 text-success-700 shadow-sme-soft dark:border-success-700/40 dark:from-success-700/20 dark:to-emerald-900/10 dark:text-success-100"
                        role="status"
                    >
                        <span>{{ session('status') }}</span>
                        <button type="button" class="ms-3 text-sm" @click="show = false" aria-label="{{ __('ui.dismiss') }}">
                            <x-lucide-x class="h-4 w-4" />
                        </button>
                    </div>
                @endif

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>

    <div
        x-show="$store.sidebar.open && window.innerWidth < 1024"
        x-transition.opacity
        class="fixed inset-0 z-30 bg-slate-950/45 backdrop-blur-[2px] lg:hidden"
        @click="$store.sidebar.closeMobile()"
        style="display: none;"
    ></div>

    @stack('modals')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                mode: localStorage.getItem('sme-theme') || 'light',
                init() { this.apply(); },
                toggle() {
                    this.mode = this.mode === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('sme-theme', this.mode);
                    this.apply();
                },
                apply() {
                    document.documentElement.classList.toggle('dark', this.mode === 'dark');
                },
            });

            Alpine.store('sidebar', {
                open: window.innerWidth >= 1024,
                collapsed: localStorage.getItem('sme-sidebar-collapsed') === '1',
                toggleMobile() { this.open = !this.open; },
                toggleCollapse() {
                    this.collapsed = !this.collapsed;
                    localStorage.setItem('sme-sidebar-collapsed', this.collapsed ? '1' : '0');
                },
                closeMobile() {
                    if (window.innerWidth < 1024) this.open = false;
                },
            });
        });
    </script>
    @livewireScripts
</body>
</html>

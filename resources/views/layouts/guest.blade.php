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
    <title>@yield('title', config('app.name'))</title>
    <script>
        (function () {
            const theme = localStorage.getItem('sme-theme') || 'light';
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-[color:var(--sme-text)]">
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 start-1/2 h-72 w-[36rem] -translate-x-1/2 rounded-full bg-gradient-to-br from-primary-400/25 via-secondary-400/15 to-transparent blur-3xl dark:from-primary-500/20 dark:via-secondary-500/10"></div>
        </div>

        <div class="relative mb-8 flex items-center gap-3">
            <div class="sme-brand-mark flex h-11 w-11 items-center justify-center rounded-xl text-white">
                <x-lucide-graduation-cap class="h-6 w-6" />
            </div>
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ __('ui.app_name') }}</p>
                <p class="text-xs text-muted">{{ __('ui.tagline') }}</p>
            </div>
        </div>

        <div class="relative w-full max-w-md">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-success-200/70 bg-gradient-to-r from-success-50 to-emerald-50/40 px-4 py-3 text-sm text-success-700 dark:border-success-700/40 dark:from-success-700/20 dark:to-emerald-900/10 dark:text-success-100">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>

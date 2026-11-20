@extends('layouts.guest')

@section('title', __('ui.login'))

@section('content')
    <div class="sme-surface-solid rounded-2xl border border-sme p-6 shadow-sme sm:p-8">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('ui.login') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.login_help') }}</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf

            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" required autocomplete="username" />
            <x-ui.input name="password" type="password" :label="__('ui.field_password')" required autocomplete="current-password" />

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-muted">
                    <input type="checkbox" name="remember" value="1" class="rounded border-sme text-primary-600 focus:ring-primary-500">
                    {{ __('ui.remember_me') }}
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('ui.forgot_password') }}</a>
            </div>

            <x-ui.button type="submit" class="w-full justify-center">{{ __('ui.sign_in') }}</x-ui.button>
        </form>

        @if (session('status'))
            <p class="mt-4 text-center text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
        @endif

        <p class="mt-6 text-center text-sm text-muted">
            {{ __('ui.no_account') }}
            <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('ui.register_institution') }}</a>
        </p>
    </div>
@endsection

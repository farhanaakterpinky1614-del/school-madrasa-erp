@extends('layouts.guest')

@section('title', __('ui.forgot_password'))

@section('content')
    <div class="sme-surface-solid rounded-2xl border border-sme p-6 shadow-sme sm:p-8">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('ui.forgot_password') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.forgot_password_help') }}</p>

        @if (session('status'))
            <p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" required autocomplete="username" />
            <x-ui.button type="submit" class="w-full justify-center">{{ __('ui.send_reset_link') }}</x-ui.button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ __('ui.back_to_login') }}</a>
        </p>
    </div>
@endsection

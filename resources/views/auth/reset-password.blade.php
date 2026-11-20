@extends('layouts.guest')

@section('title', __('ui.reset_password'))

@section('content')
    <div class="sme-surface-solid rounded-2xl border border-sme p-6 shadow-sme sm:p-8">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('ui.reset_password') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.reset_password_help') }}</p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email', $email)" required autocomplete="username" />
            <x-ui.input name="password" type="password" :label="__('ui.field_password')" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" type="password" :label="__('ui.confirm_password')" required autocomplete="new-password" />
            <x-ui.button type="submit" class="w-full justify-center">{{ __('ui.reset_password') }}</x-ui.button>
        </form>
    </div>
@endsection

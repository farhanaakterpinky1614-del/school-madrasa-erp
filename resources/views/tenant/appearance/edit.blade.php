@extends('layouts.app')

@section('title', __('ui.nav.appearance'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.appearance') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.appearance_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.form-section :title="__('ui.theme_packs')" :description="__('ui.theme_packs_help')">
        <form method="POST" action="{{ route('tenant.appearance.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($packs as $key => $pack)
                    @php
                        $locked = ($pack['premium'] ?? false) && ! $canPremium;
                    @endphp
                    <label @class([
                        'block cursor-pointer rounded-2xl border p-4 transition',
                        'border-primary-400/70 bg-primary-50/50 dark:bg-primary-950/30' => $activePack === $key,
                        'border-sme sme-surface-solid' => $activePack !== $key,
                        'opacity-60' => $locked,
                    ])>
                        <div class="flex items-start gap-3">
                            <input type="radio" name="theme_pack" value="{{ $key }}" class="mt-1" @checked($activePack === $key) @disabled($locked)>
                            <div>
                                <p class="font-semibold">{{ $pack['label'] }}</p>
                                <p class="mt-1 text-sm text-muted">{{ $pack['description'] }}</p>
                                @if ($pack['premium'] ?? false)
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-primary-600">
                                        {{ $locked ? __('ui.premium_locked') : __('ui.premium_pack') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        </form>
    </x-ui.form-section>
@endsection

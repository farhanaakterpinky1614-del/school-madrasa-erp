@extends('layouts.app')

@section('title', __('ui.nav.domains'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.domains') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.domains_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.table :headers="[__('ui.host'), __('ui.type'), __('ui.col_status'), __('ui.col_action')]">
                @foreach ($domains as $domain)
                    <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                        <td class="px-4 py-3.5 font-medium">
                            {{ $domain->host }}
                            @if ($domain->is_primary)
                                <x-ui.badge tone="success">primary</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-muted">{{ $domain->type }}</td>
                        <td class="px-4 py-3.5">
                            <x-ui.badge :tone="$domain->isVerified() ? 'success' : 'warning'">{{ $domain->verification_status }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3.5">
                            @if ($domain->type === 'custom' && ! $domain->isVerified())
                                <form method="POST" action="{{ route('tenant.domains.stub-verify', $domain) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.stub_verify') }}</x-ui.button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>

        <aside>
            <x-ui.form-section :title="__('ui.add_custom_domain')" :description="__('ui.custom_domain_todo')">
                @if ($canCustomDomain)
                    <form method="POST" action="{{ route('tenant.domains.store') }}" class="space-y-3">
                        @csrf
                        <x-ui.input name="host" :label="__('ui.host')" :value="old('host')" required :error="$errors->first('host')" hint="example.edu.bd" />
                        <x-ui.button type="submit" size="sm">{{ __('ui.add_domain') }}</x-ui.button>
                    </form>
                @else
                    <p class="text-sm text-muted">{{ __('ui.custom_domain_enterprise_only') }}</p>
                @endif
            </x-ui.form-section>
        </aside>
    </div>
@endsection

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
        <div class="lg:col-span-2 space-y-4">
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
                            <x-ui.badge :tone="$domain->isVerified() ? 'success' : ($domain->verification_status === 'failed' ? 'danger' : 'warning')">{{ $domain->verification_status }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3.5">
                            @if ($domain->type === 'custom' && ! $domain->isVerified())
                                <form method="POST" action="{{ route('tenant.domains.verify', $domain) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.verify_domain') }}</x-ui.button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @php
                $pendingCustom = $domains->first(fn ($d) => $d->type === 'custom' && ! $d->isVerified() && $d->verification_token);
            @endphp
            @if ($pendingCustom)
                <div class="rounded-2xl border border-dashed border-primary-200/70 bg-primary-50/30 p-4 text-sm dark:border-primary-500/20 dark:bg-primary-950/20">
                    <p class="font-semibold">{{ __('ui.dns_instructions_title') }}</p>
                    <p class="mt-1 text-muted">{{ __('ui.dns_instructions_help') }}</p>
                    <dl class="mt-3 space-y-2 font-mono text-xs">
                        <div>
                            <dt class="text-muted">TXT name</dt>
                            <dd class="break-all">{{ $verification->txtName($pendingCustom) }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">TXT value</dt>
                            <dd class="break-all">{{ $verification->expectedTxtValue($pendingCustom) }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">HTTP fallback</dt>
                            <dd class="break-all">https://{{ $pendingCustom->host }}{{ $verification->httpChallengePath() }} → {{ $pendingCustom->verification_token }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        <aside>
            <x-ui.form-section :title="__('ui.add_custom_domain')" :description="__('ui.custom_domain_verify_help')">
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

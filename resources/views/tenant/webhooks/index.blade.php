@extends('layouts.app')

@section('title', __('ui.nav.webhooks'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.webhooks') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.webhooks_help') }}</p>
    </div>
@endsection

@section('content')
    @if (! $canWebhooks)
        <x-ui.form-section :title="__('ui.webhooks_locked')" :description="__('ui.webhooks_locked_help')">
            <p class="text-sm text-muted">{{ __('ui.upgrade_for_webhooks') }}</p>
        </x-ui.form-section>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.table :headers="[__('ui.name'), __('ui.target_url'), __('ui.events'), __('ui.col_status'), __('ui.col_action')]">
                    @forelse ($subscriptions as $sub)
                        <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 font-medium">{{ $sub->name }}</td>
                            <td class="px-4 py-3.5 text-xs text-muted break-all">{{ $sub->target_url }}</td>
                            <td class="px-4 py-3.5 text-xs text-muted">{{ implode(', ', $sub->events ?? []) }}</td>
                            <td class="px-4 py-3.5">
                                <x-ui.badge :tone="$sub->is_active ? 'success' : 'warning'">{{ $sub->is_active ? 'active' : 'inactive' }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('tenant.webhooks.test', $sub) }}">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.test_webhook') }}</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('tenant.webhooks.destroy', $sub) }}" onsubmit="return confirm('Remove?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.delete') }}</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-sm text-muted">{{ __('ui.no_webhooks') }}</td>
                        </tr>
                    @endforelse
                </x-ui.table>

                <div>
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-muted">{{ __('ui.recent_deliveries') }}</h2>
                    <x-ui.table :headers="[__('ui.event'), __('ui.col_status'), __('ui.created'), 'HTTP']">
                        @forelse ($deliveries as $d)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $d->event }}</td>
                                <td class="px-4 py-3"><x-ui.badge :tone="$d->status === 'stubbed' ? 'success' : ($d->status === 'failed' ? 'danger' : 'warning')">{{ $d->status }}</x-ui.badge></td>
                                <td class="px-4 py-3 text-xs text-muted">{{ $d->created_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-muted">{{ $d->response_code ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-sm text-muted">{{ __('ui.no_deliveries') }}</td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </div>
            </div>

            <aside>
                <x-ui.form-section :title="__('ui.add_webhook')" :description="__('ui.add_webhook_help')">
                    <form method="POST" action="{{ route('tenant.webhooks.store') }}" class="space-y-3">
                        @csrf
                        <x-ui.input name="name" :label="__('ui.name')" :value="old('name')" required />
                        <x-ui.input name="target_url" :label="__('ui.target_url')" :value="old('target_url')" required hint="https://example.com/hooks/sme" />
                        <x-ui.input name="secret" :label="__('ui.webhook_secret')" :value="old('secret')" hint="optional — auto-generated if blank" />
                        <fieldset class="space-y-2">
                            <legend class="text-sm font-medium">{{ __('ui.events') }}</legend>
                            @foreach ($events as $event)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" class="rounded border-slate-300 text-primary-600" @checked(collect(old('events', $events))->contains($event))>
                                    <span class="font-mono text-xs">{{ $event }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                        <x-ui.button type="submit" size="sm">{{ __('ui.add_webhook') }}</x-ui.button>
                    </form>
                </x-ui.form-section>
            </aside>
        </div>
    @endif
@endsection

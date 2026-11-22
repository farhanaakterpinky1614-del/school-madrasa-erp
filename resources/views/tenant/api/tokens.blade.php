@extends('layouts.app')

@section('title', __('ui.nav.api_tokens'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.api_tokens') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.api_tokens_help') }}</p>
    </div>
@endsection

@section('content')
    @if (! $canApi)
        <x-ui.form-section :title="__('ui.api_access_locked')" :description="__('ui.api_access_locked_help')">
            <p class="text-sm text-muted">{{ __('ui.upgrade_for_api') }}</p>
        </x-ui.form-section>
    @else
        @if ($plainTextToken)
            <div class="mb-6 rounded-2xl border border-amber-300/60 bg-amber-50/80 px-4 py-3 text-sm dark:border-amber-500/30 dark:bg-amber-950/30">
                <p class="font-semibold">{{ __('ui.api_token_once') }}</p>
                <code class="mt-2 block break-all font-mono text-xs">{{ $plainTextToken }}</code>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-ui.table :headers="[__('ui.name'), __('ui.created'), __('ui.last_used'), __('ui.col_action')]">
                    @forelse ($tokens as $token)
                        <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                            <td class="px-4 py-3.5 font-medium">{{ $token->name }}</td>
                            <td class="px-4 py-3.5 text-muted">{{ $token->created_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3.5 text-muted">{{ $token->last_used_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3.5">
                                <form method="POST" action="{{ route('tenant.api.tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke this token?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.revoke') }}</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-sm text-muted">{{ __('ui.no_api_tokens') }}</td>
                        </tr>
                    @endforelse
                </x-ui.table>

                <div class="mt-6 rounded-2xl border border-dashed border-primary-200/70 bg-primary-50/30 p-4 text-sm dark:border-primary-500/20 dark:bg-primary-950/20">
                    <p class="font-semibold">{{ __('ui.api_docs_title') }}</p>
                    <p class="mt-1 text-muted">{{ __('ui.api_docs_help') }}</p>
                    <ul class="mt-3 list-inside list-disc space-y-1 font-mono text-xs text-muted">
                        <li>GET /api/v1/students</li>
                        <li>POST /api/v1/students</li>
                        <li>GET|POST /api/v1/attendance</li>
                        <li>GET /api/v1/fees/summary</li>
                        <li>POST /api/v1/fees/invoices/{id}/payments</li>
                    </ul>
                </div>
            </div>

            <aside>
                <x-ui.form-section :title="__('ui.create_api_token')" :description="__('ui.create_api_token_help')">
                    <form method="POST" action="{{ route('tenant.api.tokens.store') }}" class="space-y-3">
                        @csrf
                        <x-ui.input name="name" :label="__('ui.name')" :value="old('name')" required :error="$errors->first('name')" hint="mobile-app, accounting-sync…" />
                        <x-ui.button type="submit" size="sm">{{ __('ui.create_token') }}</x-ui.button>
                    </form>
                </x-ui.form-section>
            </aside>
        </div>
    @endif
@endsection

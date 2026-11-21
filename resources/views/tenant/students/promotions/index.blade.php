@extends('layouts.app')
@section('title', __('ui.nav.promotions'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.promotions') }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ __('ui.promotions_help') }}</p>
</div>
@endsection
@section('content')
<x-ui.table :headers="[__('ui.col_name'), __('ui.from'), __('ui.to'), __('ui.col_status')]">
@forelse($promotions as $p)
<tr>
<td class="px-4 py-3 font-medium">{{ $p->student?->full_name }}</td>
<td class="px-4 py-3 text-muted">{{ $p->fromYear?->name }} / {{ $p->fromClass?->name }}</td>
<td class="px-4 py-3 text-muted">{{ $p->toYear?->name }} / {{ $p->toClass?->name }}</td>
<td class="px-4 py-3">{{ $p->status }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
@endforelse
</x-ui.table>
<div class="mt-4">{{ $promotions->links() }}</div>
@endsection

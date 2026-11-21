@extends('layouts.app')
@section('title', __('ui.nav.transfers'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.transfers') }}</h1>
</div>
@endsection
@section('content')
<x-ui.table :headers="[__('ui.col_name'), __('ui.from'), __('ui.to'), __('ui.date')]">
@forelse($transfers as $t)
<tr>
<td class="px-4 py-3 font-medium">{{ $t->student?->full_name }}</td>
<td class="px-4 py-3 text-muted">{{ $t->fromClass?->name }} @if($t->fromSection)/ {{ $t->fromSection->name }}@endif</td>
<td class="px-4 py-3 text-muted">{{ $t->toClass?->name }} @if($t->toSection)/ {{ $t->toSection->name }}@endif</td>
<td class="px-4 py-3">{{ optional($t->transferred_on)->format('Y-m-d') }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
@endforelse
</x-ui.table>
<div class="mt-4">{{ $transfers->links() }}</div>
@endsection

@extends('layouts.app')
@section('title', __('ui.nav.materials'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.materials') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.materials_help') }}</p>
    </div>
    <a href="{{ route('tenant.materials.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.material_type'), __('ui.nav.classes'), __('ui.nav.subjects'), __('ui.col_action')]">
        @forelse ($materials as $material)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $material->title }}
                    @if($material->external_url)<p class="text-xs text-primary-600"><a href="{{ $material->external_url }}" target="_blank" rel="noopener">{{ $material->external_url }}</a></p>@endif
                </td>
                <td class="px-4 py-3 text-muted">{{ __('ui.material_type_'.$material->material_type) }}</td>
                <td class="px-4 py-3 text-muted">{{ $material->schoolClass?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $material->subject?->name ?: '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.materials.destroy', $material) }}">@csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $materials->links() }}</div>
@endsection

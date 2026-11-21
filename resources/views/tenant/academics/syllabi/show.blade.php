@extends('layouts.app')
@section('title', $syllabus->title)
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $syllabus->title }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $syllabus->schoolClass?->name }} · {{ $syllabus->subject?->name }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tenant.syllabi.edit', $syllabus) }}"><x-ui.button type="button" variant="secondary">{{ __('ui.edit') }}</x-ui.button></a>
        <a href="{{ route('tenant.syllabi.index') }}" class="text-sm font-medium text-muted self-center">{{ __('ui.back') }}</a>
    </div>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($syllabus->description)
        <p class="mb-6 text-sm text-muted">{{ $syllabus->description }}</p>
    @endif
    <div class="mb-8">
        <h2 class="mb-3 text-lg font-semibold">{{ __('ui.topics') }}</h2>
        <x-ui.table :headers="[__('ui.col_name'), __('ui.sort_order'), __('ui.estimated_periods'), __('ui.col_action')]">
            @forelse ($syllabus->topics as $topic)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $topic->title }}
                        @if($topic->description)<p class="mt-1 text-xs text-muted">{{ $topic->description }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-muted">{{ $topic->sort_order }}</td>
                    <td class="px-4 py-3 text-muted">{{ $topic->estimated_periods ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('tenant.syllabi.topics.destroy', [$syllabus, $topic]) }}">@csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
            @endforelse
        </x-ui.table>
    </div>
    <form method="POST" action="{{ route('tenant.syllabi.topics.store', $syllabus) }}" class="max-w-xl space-y-3 rounded-2xl sme-surface p-5">@csrf
        <h3 class="font-semibold">{{ __('ui.add_topic') }}</h3>
        <x-ui.input name="title" :label="__('ui.col_name')" required />
        <x-ui.input name="sort_order" type="number" :label="__('ui.sort_order')" :value="0" />
        <x-ui.input name="estimated_periods" type="number" :label="__('ui.estimated_periods')" />
        <label class="block text-sm font-medium">{{ __('ui.description') }}</label>
        <textarea name="description" rows="2" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm"></textarea>
        <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
    </form>
@endsection

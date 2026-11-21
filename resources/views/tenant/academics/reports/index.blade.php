@extends('layouts.app')
@section('title', __('ui.nav.academic_reports'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.academic_reports') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.academic_reports_help') }}</p>
    </div>
@endsection
@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        @foreach ([
            ['label' => __('ui.nav.subjects'), 'value' => $stats['subjects']],
            ['label' => __('ui.nav.rooms'), 'value' => $stats['rooms']],
            ['label' => __('ui.nav.routines'), 'value' => $stats['routines']],
            ['label' => __('ui.nav.syllabi'), 'value' => $stats['syllabi']],
            ['label' => __('ui.nav.lesson_plans'), 'value' => $stats['lesson_plans_published'].' / '.$stats['lesson_plans']],
            ['label' => __('ui.nav.homework'), 'value' => $stats['homework_open'].' / '.$stats['homework']],
            ['label' => __('ui.nav.classwork'), 'value' => $stats['classwork']],
            ['label' => __('ui.nav.materials'), 'value' => $stats['materials']],
        ] as $card)
            <div class="rounded-2xl sme-surface p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-lg font-semibold">{{ __('ui.upcoming_homework') }}</h2>
            <x-ui.table :headers="[__('ui.col_name'), __('ui.nav.classes'), __('ui.due_on')]">
                @forelse ($upcomingHomework as $hw)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $hw->title }}</td>
                        <td class="px-4 py-3 text-muted">{{ $hw->schoolClass?->name }}</td>
                        <td class="px-4 py-3 text-muted">{{ optional($hw->due_on)->toDateString() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </div>
        <div>
            <h2 class="mb-3 text-lg font-semibold">{{ __('ui.recent_lesson_plans') }}</h2>
            <x-ui.table :headers="[__('ui.col_name'), __('ui.nav.subjects'), __('ui.planned_on')]">
                @forelse ($recentPlans as $plan)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $plan->title }}</td>
                        <td class="px-4 py-3 text-muted">{{ $plan->subject?->name }}</td>
                        <td class="px-4 py-3 text-muted">{{ optional($plan->planned_on)->toDateString() ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
@endsection

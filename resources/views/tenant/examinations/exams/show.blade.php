@extends('layouts.app')

@section('title', $exam->name)

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $exam->name }}</h1>
        <p class="mt-1.5 text-sm text-muted">
            {{ $exam->schoolClass?->name }}
            · {{ __('ui.exam_status_'.$exam->status) }}
            @if ($isPublished)
                · <span class="font-semibold text-emerald-700">{{ __('ui.published') }}</span>
            @else
                · <span class="text-amber-700">{{ __('ui.unpublished') }}</span>
            @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @can('exams.exam.manage')
            <a href="{{ route('tenant.exams.edit', $exam) }}"><x-ui.button type="button" variant="secondary">{{ __('ui.edit') }}</x-ui.button></a>
        @endcan
        @can('exams.marks.manage')
            <a href="{{ route('tenant.marks.index', ['exam_id' => $exam->id]) }}"><x-ui.button type="button">{{ __('ui.enter_marks') }}</x-ui.button></a>
        @endcan
        @can('exams.pdf.export')
            <a href="{{ route('tenant.results.pdf.index', ['exam_id' => $exam->id]) }}"><x-ui.button type="button" variant="outline">{{ __('ui.pdf_exports') }}</x-ui.button></a>
        @endcan
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950 lg:col-span-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.exam_details') }}</h2>
            <dl class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-muted">{{ __('ui.nav.years') }}</dt><dd class="font-medium">{{ $exam->academicYear?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.nav.terms') }}</dt><dd class="font-medium">{{ $exam->academicTerm?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.exam_dates') }}</dt><dd class="font-medium">{{ $exam->starts_on?->format('d M Y') ?? '—' }} → {{ $exam->ends_on?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.nav.grading_schemes') }}</dt><dd class="font-medium">{{ $exam->gradingScheme?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.board_name') }}</dt><dd class="font-medium">{{ $exam->board_name ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.board_roll_prefix') }}</dt><dd class="font-medium">{{ $exam->board_roll_prefix ?: '—' }}</dd></div>
            </dl>
            @if ($exam->gradingScheme?->gradingScale)
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.grade_bands') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($exam->gradingScheme->gradingScale->bands as $band)
                            <span class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-900">
                                <strong>{{ $band->grade }}</strong>
                                {{ $band->min_mark }}–{{ $band->max_mark }}
                                @if ($band->gpa) · GPA {{ $band->gpa }}@endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-4 text-white">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-indigo-200">{{ __('ui.quick_links') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @can('exams.publish.manage')
                    <li><a class="text-indigo-100 underline-offset-2 hover:underline" href="{{ route('tenant.results.publish.index', ['exam_id' => $exam->id]) }}">{{ __('ui.nav.publish_results') }}</a></li>
                @endcan
                @can('exams.marks.view')
                    <li><a class="text-indigo-100 underline-offset-2 hover:underline" href="{{ route('tenant.marks.index', ['exam_id' => $exam->id]) }}">{{ __('ui.nav.marks_entry') }}</a></li>
                @endcan
            </ul>
        </div>
    </div>

    <div class="mb-8">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold tracking-tight">{{ __('ui.exam_subjects') }}</h2>
        </div>

        <x-ui.table :headers="[__('ui.nav.subjects'), __('ui.full_marks'), __('ui.pass_marks'), __('ui.weight'), __('ui.col_action')]">
            @forelse ($exam->subjects as $paper)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $paper->subject?->name }}</td>
                    <td class="px-4 py-3">{{ $paper->full_marks }}</td>
                    <td class="px-4 py-3">{{ $paper->pass_marks }}</td>
                    <td class="px-4 py-3 text-muted">{{ $paper->weight ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @can('exams.subject.manage')
                            <form method="POST" action="{{ route('tenant.exams.subjects.destroy', [$exam, $paper]) }}" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_exam_subjects') }}</td></tr>
            @endforelse
        </x-ui.table>

        @can('exams.subject.manage')
            <form method="POST" action="{{ route('tenant.exams.subjects.store', $exam) }}" class="mt-4 grid gap-3 rounded-2xl border border-dashed border-slate-300 bg-white/70 p-4 sm:grid-cols-6 dark:border-slate-600 dark:bg-slate-900/40">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.subjects') }}</label>
                    <select name="subject_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.input name="full_marks" type="number" step="0.01" :label="__('ui.full_marks')" value="100" required />
                <x-ui.input name="pass_marks" type="number" step="0.01" :label="__('ui.pass_marks')" value="33" required />
                <x-ui.input name="weight" type="number" step="0.01" :label="__('ui.weight')" />
                <div class="flex items-end"><x-ui.button type="submit" size="sm">{{ __('ui.add_paper') }}</x-ui.button></div>
            </form>
        @endcan
    </div>
@endsection

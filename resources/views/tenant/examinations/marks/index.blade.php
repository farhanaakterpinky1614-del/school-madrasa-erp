@extends('layouts.app')

@section('title', __('ui.nav.marks_entry'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.marks_entry') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.marks_entry_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($scoped ?? false)
        <p class="mb-4 rounded-xl border border-slate-200 bg-gradient-to-r from-indigo-50/80 to-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900 dark:text-slate-200">
            {{ __('ui.exam_teacher_scope_hint') }}
        </p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.exams') }}</label>
            <select name="exam_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @foreach ($exams as $ex)
                    <option value="{{ $ex->id }}" @selected((int) $examId === (int) $ex->id)>{{ $ex->name }} ({{ $ex->schoolClass?->name }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.paper') }}</label>
            <select name="exam_subject_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                @forelse ($papers as $p)
                    <option value="{{ $p->id }}" @selected((int) $paperId === (int) $p->id)>{{ $p->subject?->name }} ({{ $p->full_marks }})</option>
                @empty
                    <option value="">{{ __('ui.no_exam_subjects') }}</option>
                @endforelse
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.sections') }}</label>
            <select name="section_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_sections') }}</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((int) $sectionId === (int) $section->id)>{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.load_register') }}</x-ui.button></div>
    </form>

    @if (! $exam || ! $paper)
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.select_exam_paper') }}</p>
    @elseif ($students->isEmpty())
        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.no_enrolled_students') }}</p>
    @else
        <form method="POST" action="{{ route('tenant.marks.store') }}" class="space-y-4" x-data="{ focusIndex: 0 }">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}" />
            <input type="hidden" name="exam_subject_id" value="{{ $paper->id }}" />
            <input type="hidden" name="section_id" value="{{ $sectionId }}" />

            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-muted">
                    {{ $paper->subject?->name }} · {{ __('ui.full_marks') }}: {{ $paper->full_marks }} · {{ __('ui.pass_marks') }}: {{ $paper->pass_marks }}
                </p>
                @can('exams.marks.manage')
                    <x-ui.button type="submit">{{ __('ui.save_marks') }}</x-ui.button>
                @endcan
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50/80 dark:bg-slate-900/60">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold">#</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.col_name') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.marks') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.absent') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.grade') }}</th>
                            <th class="px-4 py-3 text-start font-semibold">{{ __('ui.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($students as $i => $student)
                            @php $row = $existing->get($student->id); @endphp
                            <tr class="bg-white/80 hover:bg-indigo-50/40 dark:bg-slate-950/40 dark:hover:bg-slate-900">
                                <td class="px-4 py-3 text-muted">{{ $student->roll_number ?? ($i + 1) }}</td>
                                <td class="px-4 py-3 font-medium">
                                    {{ $student->full_name }}
                                    <span class="ms-2 text-xs text-muted">{{ $student->student_code }}</span>
                                    <input type="hidden" name="entries[{{ $i }}][student_id]" value="{{ $student->id }}" />
                                    <input type="hidden" name="entries[{{ $i }}][entry_status]" value="draft" />
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $paper->full_marks }}"
                                        name="entries[{{ $i }}][marks_obtained]"
                                        value="{{ old('entries.'.$i.'.marks_obtained', $row?->marks_obtained) }}"
                                        class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900"
                                        @can('exams.marks.manage') @else disabled @endcan
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="entries[{{ $i }}][is_absent]" value="1" @checked(old('entries.'.$i.'.is_absent', $row?->is_absent)) @can('exams.marks.manage') @else disabled @endcan />
                                        {{ __('ui.absent') }}
                                    </label>
                                </td>
                                <td class="px-4 py-3 font-semibold text-indigo-700 dark:text-indigo-300">{{ $row?->grade ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <input
                                        type="text"
                                        name="entries[{{ $i }}][remarks]"
                                        value="{{ old('entries.'.$i.'.remarks', $row?->remarks) }}"
                                        class="w-full max-w-[12rem] rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900"
                                        @can('exams.marks.manage') @else disabled @endcan
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @can('exams.marks.manage')
                <div class="flex justify-end"><x-ui.button type="submit">{{ __('ui.save_marks') }}</x-ui.button></div>
            @endcan
        </form>
    @endif
@endsection

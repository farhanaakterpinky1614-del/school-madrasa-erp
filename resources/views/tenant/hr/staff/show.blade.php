@extends('layouts.app')

@section('title', $staff->full_name)

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $staff->full_name }}</h1>
        <p class="mt-1.5 font-mono text-sm text-muted">{{ $staff->employee_code }}</p>
    </div>
    <a href="{{ route('tenant.staff.edit', $staff) }}"><x-ui.button type="button" variant="secondary">{{ __('ui.edit') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-3 rounded-2xl border border-slate-200/80 bg-white/70 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('ui.profile') }}</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-muted">{{ __('ui.designation') }}</dt><dd class="font-medium">{{ $staff->designation?->name ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.department') }}</dt><dd class="font-medium">{{ $staff->department?->name ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.staff_type') }}</dt><dd class="font-medium">{{ __('ui.staff_type_'.$staff->staff_type) }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.col_status') }}</dt><dd><x-ui.badge :tone="$staff->status === 'active' ? 'success' : 'warning'">{{ __('ui.staff_status_'.$staff->status) }}</x-ui.badge></dd></div>
                <div><dt class="text-muted">{{ __('ui.phone') }}</dt><dd class="font-medium">{{ $staff->phone ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.email') }}</dt><dd class="font-medium">{{ $staff->email ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.joined_on') }}</dt><dd class="font-medium">{{ optional($staff->joined_on)->format('Y-m-d') ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.nav.branches') }}</dt><dd class="font-medium">{{ $staff->branch?->name ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="space-y-3 rounded-2xl border border-slate-200/80 bg-white/70 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('ui.current_salary') }}</h2>
            @if ($staff->currentSalary?->salaryStructure)
                @php $ss = $staff->currentSalary->salaryStructure; @endphp
                <p class="text-sm font-medium">{{ $ss->name }}</p>
                <p class="text-2xl font-semibold text-primary-700">৳ {{ number_format($ss->net(), 2) }}</p>
                <p class="text-xs text-muted">{{ __('ui.basic_salary') }}: ৳ {{ number_format($staff->currentSalary->basic_override ?? $ss->basic_salary, 2) }}</p>
            @else
                <p class="text-sm text-muted">{{ __('ui.no_records') }}</p>
            @endif
        </section>

        @if ($staff->is_teacher)
            <section class="space-y-3 rounded-2xl border border-slate-200/80 bg-white/70 p-5 shadow-sm lg:col-span-2 dark:border-slate-700 dark:bg-slate-900/40">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('ui.nav.assignments') }}</h2>
                <x-ui.table :headers="[__('ui.nav.classes'), __('ui.nav.sections'), __('ui.nav.subjects'), __('ui.is_class_teacher')]">
                    @forelse ($staff->assignments as $a)
                        <tr>
                            <td class="px-4 py-3">{{ $a->schoolClass?->name }}</td>
                            <td class="px-4 py-3 text-muted">{{ $a->section?->name ?: '—' }}</td>
                            <td class="px-4 py-3 text-muted">{{ $a->subject?->name ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $a->is_class_teacher ? __('ui.yes') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
                    @endforelse
                </x-ui.table>
            </section>
        @endif

        <section class="space-y-3 rounded-2xl border border-slate-200/80 bg-white/70 p-5 shadow-sm lg:col-span-2 dark:border-slate-700 dark:bg-slate-900/40">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('ui.documents') }}</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($staff->documents as $doc)
                    <li class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 px-3 py-2 dark:border-slate-700">
                        <span>{{ $doc->title }} <span class="text-muted">({{ $doc->doc_type ?: 'file' }})</span></span>
                        <form method="POST" action="{{ route('tenant.staff.documents.destroy', [$staff, $doc]) }}">@csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    </li>
                @empty
                    <li class="text-muted">{{ __('ui.no_records') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('tenant.staff.documents.store', $staff) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-3">
                @csrf
                <x-ui.input name="title" :label="__('ui.label')" required />
                <x-ui.input name="doc_type" :label="__('ui.field_type')" />
                <div>
                    <label class="mb-1.5 block text-sm font-medium">{{ __('ui.file') ?? 'File' }}</label>
                    <input type="file" name="file" required class="block w-full text-sm" />
                </div>
                <div class="sm:col-span-3"><x-ui.button type="submit" size="sm">{{ __('ui.upload') ?? 'Upload' }}</x-ui.button></div>
            </form>
        </section>
    </div>
@endsection

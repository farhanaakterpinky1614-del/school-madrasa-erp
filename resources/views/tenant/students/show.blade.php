@extends('layouts.app')
@section('title', $student->full_name)
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">{{ $student->student_code }}</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $student->full_name }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.admission_number') }}: {{ $student->admission_number ?: '—' }} · <x-ui.badge :tone="$student->status === 'active' ? 'success' : 'warning'">{{ __('ui.student_status_'.$student->status) }}</x-ui.badge></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.students.id-card', $student) }}"><x-ui.button type="button" variant="outline">{{ __('ui.id_card') }}</x-ui.button></a>
        <a href="{{ route('tenant.students.edit', $student) }}"><x-ui.button type="button" variant="secondary">{{ __('ui.edit') }}</x-ui.button></a>
    </div>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.form-section :title="__('ui.student_profile')">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-muted">{{ __('ui.gender') }}</dt><dd>{{ $student->gender ? __('ui.gender_'.$student->gender) : '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.date_of_birth') }}</dt><dd>{{ optional($student->date_of_birth)->format('Y-m-d') ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.phone') }}</dt><dd>{{ $student->phone ?: '—' }}</dd></div>
                <div><dt class="text-muted">{{ __('ui.field_email') }}</dt><dd>{{ $student->email ?: '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-muted">{{ __('ui.address') }}</dt><dd>{{ $student->address ?: '—' }}</dd></div>
            </dl>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.current_enrollment')">
            @if($student->currentEnrollment)
                <p class="text-sm"><span class="font-medium">{{ $student->currentEnrollment->schoolClass?->name }}</span>
                    @if($student->currentEnrollment->section) / {{ $student->currentEnrollment->section->name }} @endif
                </p>
                <p class="mt-1 text-sm text-muted">{{ $student->currentEnrollment->academicYear?->name }} · {{ __('ui.roll_number') }}: {{ $student->currentEnrollment->roll_number ?: '—' }}</p>
            @else
                <p class="text-sm text-muted">{{ __('ui.no_enrollment') }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('tenant.students.enrollments.create', $student) }}"><x-ui.button type="button" size="sm">{{ __('ui.enroll') }}</x-ui.button></a>
                <a href="{{ route('tenant.students.transfers.create', $student) }}"><x-ui.button type="button" size="sm" variant="outline">{{ __('ui.transfer') }}</x-ui.button></a>
                <a href="{{ route('tenant.students.promotions.create', $student) }}"><x-ui.button type="button" size="sm" variant="outline">{{ __('ui.promote') }}</x-ui.button></a>
            </div>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.nav.guardians')">
            <ul class="space-y-2 text-sm">
                @forelse($student->guardians as $g)
                    <li class="flex justify-between gap-3 border-b border-sme/60 py-2">
                        <span><span class="font-medium">{{ $g->name }}</span> <span class="text-muted">({{ $g->pivot->relationship }})</span></span>
                        <span class="text-muted">{{ $g->phone }}</span>
                    </li>
                @empty
                    <li class="text-muted">{{ __('ui.no_records') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('tenant.students.guardians.attach', $student) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <x-ui.select name="guardian_id" :label="__('ui.nav.guardians')" required>
                    <option value="">—</option>
                    @foreach(\Modules\Students\Models\Guardian::query()->orderBy('name')->limit(100)->get() as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="relationship" :label="__('ui.relationship')" value="guardian" required />
                <div class="sm:col-span-2"><x-ui.button type="submit" size="sm" variant="secondary">{{ __('ui.link_guardian') }}</x-ui.button>
                <a href="{{ route('tenant.guardians.create', ['student_id' => $student->id]) }}" class="ms-3 text-sm font-semibold text-primary-600">{{ __('ui.new_guardian') }}</a></div>
            </form>
        </x-ui.form-section>

        <x-ui.form-section :title="__('ui.documents')">
            <ul class="space-y-2 text-sm">
                @forelse($student->documents as $doc)
                    <li class="flex items-center justify-between gap-3 border-b border-sme/60 py-2">
                        <span>{{ $doc->title }} <span class="text-muted">({{ $doc->doc_type }})</span></span>
                        <form method="POST" action="{{ route('tenant.students.documents.destroy', [$student, $doc]) }}">@csrf @method('DELETE')
                            <button class="text-sm font-semibold text-danger-600" type="submit">{{ __('ui.delete') }}</button>
                        </form>
                    </li>
                @empty
                    <li class="text-muted">{{ __('ui.no_records') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('tenant.students.documents.store', $student) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <x-ui.input name="title" :label="__('ui.col_name')" required />
                <x-ui.select name="doc_type" :label="__('ui.doc_type')">
                    @foreach(['birth_certificate','photo','transfer_certificate','other'] as $t)
                        <option value="{{ $t }}">{{ __('ui.doc_type_'.$t) }}</option>
                    @endforeach
                </x-ui.select>
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('ui.file') }}</label>
                    <input type="file" name="file" required class="block w-full text-sm" />
                </div>
                <x-ui.button type="submit" size="sm">{{ __('ui.upload') }}</x-ui.button>
            </form>
        </x-ui.form-section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-ui.form-section :title="__('ui.withdrawal_alumni')">
            <form method="POST" action="{{ route('tenant.students.withdraw', $student) }}" class="space-y-3">
                @csrf
                <x-ui.input name="withdrawn_on" type="date" :label="__('ui.withdrawn_on')" :value="now()->toDateString()" />
                <x-ui.textarea name="reason" :label="__('ui.reason')">{{ old('reason') }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="mark_alumni" value="1"> {{ __('ui.mark_alumni') }}</label>
                <x-ui.button type="submit" variant="danger" size="sm">{{ __('ui.withdraw') }}</x-ui.button>
            </form>
            <form method="POST" action="{{ route('tenant.students.alumni', $student) }}" class="mt-4">@csrf
                <x-ui.button type="submit" variant="outline" size="sm">{{ __('ui.mark_alumni_only') }}</x-ui.button>
            </form>
        </x-ui.form-section>
        <x-ui.form-section :title="__('ui.enrollment_history')">
            <ul class="space-y-2 text-sm">
                @foreach($student->enrollments as $en)
                    <li class="border-b border-sme/60 py-2">
                        {{ $en->academicYear?->name }} · {{ $en->schoolClass?->name }}
                        @if($en->section)/ {{ $en->section->name }}@endif
                        · <span class="text-muted">{{ $en->status }}</span>
                    </li>
                @endforeach
            </ul>
        </x-ui.form-section>
    </div>
@endsection

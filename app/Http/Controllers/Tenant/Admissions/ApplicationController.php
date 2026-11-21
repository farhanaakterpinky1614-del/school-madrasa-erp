<?php

namespace App\Http\Controllers\Tenant\Admissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;
use Modules\Admissions\Models\AdmissionApplication;
use Modules\Admissions\Models\AdmissionSession;
use Modules\Admissions\Services\AdmissionWorkflowService;
use Modules\Students\Services\StudentIdGenerator;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = AdmissionApplication::query()
            ->with(['session', 'schoolClass', 'student'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('channel')->toString(), fn ($q, $channel) => $q->where('channel', $channel))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tenant.admissions.applications.index', compact('applications'));
    }

    public function show(AdmissionApplication $application): View
    {
        $this->assertTenantOwned($application);
        $application->load(['session.formTemplate.fields', 'schoolClass', 'student', 'documents', 'reviewer']);

        return view('tenant.admissions.applications.show', [
            'application' => $application,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function offlineCreate(): View
    {
        return view('tenant.admissions.offline.create', [
            'sessions' => AdmissionSession::query()->where('is_active', true)->where('allow_offline', true)->orderByDesc('opens_at')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
        ]);
    }

    public function offlineStore(Request $request, StudentIdGenerator $ids): RedirectResponse
    {
        $data = $request->validate([
            'admission_session_id' => ['required', 'integer'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:32'],
            'guardian_relation' => ['nullable', 'string', 'max:32'],
            'school_class_id' => ['nullable', 'integer'],
            'custom' => ['nullable', 'array'],
        ]);

        $session = AdmissionSession::query()->findOrFail($data['admission_session_id']);
        $this->assertTenantOwned($session);
        abort_unless($session->allow_offline && $session->is_active, 422);

        $application = AdmissionApplication::query()->create([
            'admission_session_id' => $session->id,
            'application_number' => $ids->nextApplicationNumber(),
            'channel' => 'offline',
            'status' => AdmissionApplication::STATUS_SUBMITTED,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'name_bn' => $data['name_bn'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'guardian_name' => $data['guardian_name'] ?? null,
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'guardian_relation' => $data['guardian_relation'] ?? null,
            'school_class_id' => $data['school_class_id'] ?? $session->school_class_id,
            'form_payload' => $data['custom'] ?? [],
            'submitted_at' => now(),
        ]);

        return redirect()->route('tenant.applications.show', $application)->with('status', __('ui.application_saved'));
    }

    public function review(AdmissionApplication $application, AdmissionWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($application);
        $workflow->markUnderReview($application, auth()->id());

        return redirect()->route('tenant.applications.show', $application)->with('status', __('ui.application_under_review'));
    }

    public function approve(Request $request, AdmissionApplication $application, AdmissionWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($application);
        $notes = $request->validate(['decision_notes' => ['nullable', 'string']])['decision_notes'] ?? null;
        $workflow->approve($application, auth()->id(), $notes);

        return redirect()->route('tenant.applications.show', $application)->with('status', __('ui.application_approved'));
    }

    public function reject(Request $request, AdmissionApplication $application, AdmissionWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($application);
        $notes = $request->validate(['decision_notes' => ['nullable', 'string']])['decision_notes'] ?? null;
        $workflow->reject($application, auth()->id(), $notes);

        return redirect()->route('tenant.applications.show', $application)->with('status', __('ui.application_rejected'));
    }

    public function enroll(Request $request, AdmissionApplication $application, AdmissionWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($application);
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'school_class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'shift_id' => ['nullable', 'integer'],
            'roll_number' => ['nullable', 'string', 'max:32'],
        ]);

        $workflow->enroll($application, $data, auth()->id());

        return redirect()->route('tenant.applications.show', $application)->with('status', __('ui.application_enrolled'));
    }
}

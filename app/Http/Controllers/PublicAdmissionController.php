<?php

namespace App\Http\Controllers;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Admissions\Models\AdmissionApplication;
use Modules\Admissions\Models\AdmissionSession;
use Modules\Students\Services\StudentIdGenerator;
use Modules\Tenancy\Models\Tenant;

class PublicAdmissionController extends Controller
{
    public function show(string $tenantSlug, string $sessionSlug, CurrentTenant $currentTenant): View
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->where('status', 'active')->firstOrFail();
        $currentTenant->set($tenant);

        $session = AdmissionSession::query()
            ->where('public_slug', $sessionSlug)
            ->with(['formTemplate.fields', 'schoolClass', 'academicYear'])
            ->firstOrFail();

        abort_unless($session->isOpenForOnline(), 403);

        return view('public.admissions.apply', [
            'tenant' => $tenant,
            'session' => $session,
            'fields' => $session->formTemplate?->fields
                ?->filter(fn ($f) => $f->is_visible && in_array($f->applies_to, ['online', 'both'], true))
                ?? collect(),
        ]);
    }

    public function store(
        Request $request,
        string $tenantSlug,
        string $sessionSlug,
        CurrentTenant $currentTenant,
        StudentIdGenerator $ids,
    ): RedirectResponse {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->where('status', 'active')->firstOrFail();
        $currentTenant->set($tenant);

        $session = AdmissionSession::query()->where('public_slug', $sessionSlug)->with('formTemplate.fields')->firstOrFail();
        abort_unless($session->isOpenForOnline(), 403);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:32'],
            'guardian_relation' => ['nullable', 'string', 'max:32'],
            'custom' => ['nullable', 'array'],
        ]);

        $custom = $data['custom'] ?? [];
        foreach ($session->formTemplate?->fields ?? [] as $field) {
            if (! $field->is_visible || ! in_array($field->applies_to, ['online', 'both'], true)) {
                continue;
            }
            if ($field->is_required && empty($custom[$field->field_key])) {
                return back()->withErrors(['custom.'.$field->field_key => $field->label.' is required.'])->withInput();
            }
        }

        $application = AdmissionApplication::query()->create([
            'admission_session_id' => $session->id,
            'application_number' => $ids->nextApplicationNumber(),
            'channel' => 'online',
            'status' => AdmissionApplication::STATUS_SUBMITTED,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'guardian_name' => $data['guardian_name'],
            'guardian_phone' => $data['guardian_phone'],
            'guardian_relation' => $data['guardian_relation'] ?? 'guardian',
            'school_class_id' => $session->school_class_id,
            'form_payload' => $custom,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('public.admissions.thanks', [$tenantSlug, $sessionSlug, $application->application_number])
            ->with('status', __('ui.online_application_received'));
    }

    public function thanks(string $tenantSlug, string $sessionSlug, string $applicationNumber, CurrentTenant $currentTenant): View
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->firstOrFail();
        $currentTenant->set($tenant);

        $application = AdmissionApplication::query()
            ->where('application_number', $applicationNumber)
            ->firstOrFail();

        return view('public.admissions.thanks', compact('tenant', 'application'));
    }
}

<?php

namespace App\Http\Controllers\Tenant\Admissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Admissions\Models\AdmissionFormTemplate;
use Modules\Admissions\Models\AdmissionSession;

class AdmissionSessionController extends Controller
{
    public function index(): View
    {
        $sessions = AdmissionSession::query()
            ->with(['academicYear', 'schoolClass', 'formTemplate'])
            ->withCount('applications')
            ->orderByDesc('opens_at')
            ->paginate(20);

        return view('tenant.admissions.sessions.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('tenant.admissions.sessions.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $session = AdmissionSession::query()->create($data);

        return redirect()->route('tenant.admission-sessions.index')->with('status', __('ui.admission_session_saved'));
    }

    public function edit(AdmissionSession $admission_session): View
    {
        $this->assertTenantOwned($admission_session);

        return view('tenant.admissions.sessions.edit', array_merge($this->formData(), ['session' => $admission_session]));
    }

    public function update(Request $request, AdmissionSession $admission_session): RedirectResponse
    {
        $this->assertTenantOwned($admission_session);
        $admission_session->update($this->validated($request));

        return redirect()->route('tenant.admission-sessions.index')->with('status', __('ui.admission_session_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'public_slug' => ['nullable', 'string', 'max:64'],
            'academic_year_id' => ['required', 'integer'],
            'school_class_id' => ['nullable', 'integer'],
            'admission_form_template_id' => ['nullable', 'integer'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'allow_online' => ['sometimes', 'boolean'],
            'allow_offline' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['code'] = ($data['code'] ?? null) ?: null;
        $data['public_slug'] = ($data['public_slug'] ?? null) ?: Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['allow_online'] = $request->boolean('allow_online', true);
        $data['allow_offline'] = $request->boolean('allow_offline', true);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'templates' => AdmissionFormTemplate::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}

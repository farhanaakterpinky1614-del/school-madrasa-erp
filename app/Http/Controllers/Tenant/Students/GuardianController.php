<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Students\Models\Guardian;
use Modules\Students\Models\Student;

class GuardianController extends Controller
{
    public function index(): View
    {
        $guardians = Guardian::query()->withCount('students')->orderBy('name')->paginate(20);

        return view('tenant.students.guardians.index', compact('guardians'));
    }

    public function create(Request $request): View
    {
        $students = Student::query()->orderBy('first_name')->limit(200)->get();
        $preselect = $request->integer('student_id') ?: null;

        return view('tenant.students.guardians.create', compact('students', 'preselect'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'nid_no' => ['nullable', 'string', 'max:64'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'integer'],
            'relationship' => ['nullable', 'string', 'max:32'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_emergency' => ['sometimes', 'boolean'],
            'can_pickup' => ['sometimes', 'boolean'],
        ]);

        $guardian = Guardian::query()->create(collect($data)->only([
            'name', 'name_bn', 'phone', 'email', 'nid_no', 'occupation', 'address',
        ])->all());

        if (! empty($data['student_id'])) {
            $student = Student::query()->findOrFail($data['student_id']);
            $this->assertTenantOwned($student);
            $student->guardians()->attach($guardian->id, [
                'tenant_id' => $guardian->tenant_id,
                'relationship' => $data['relationship'] ?? 'guardian',
                'is_primary' => $request->boolean('is_primary', true),
                'is_emergency' => $request->boolean('is_emergency', true),
                'can_pickup' => $request->boolean('can_pickup', true),
            ]);
        }

        return redirect()->route('tenant.guardians.index')->with('status', __('ui.guardian_saved'));
    }

    public function edit(Guardian $guardian): View
    {
        $this->assertTenantOwned($guardian);
        $guardian->load('students');
        $students = Student::query()->orderBy('first_name')->limit(200)->get();

        return view('tenant.students.guardians.edit', compact('guardian', 'students'));
    }

    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $this->assertTenantOwned($guardian);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'nid_no' => ['nullable', 'string', 'max:64'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
        $guardian->update($data);

        return redirect()->route('tenant.guardians.index')->with('status', __('ui.guardian_saved'));
    }

    public function attach(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'guardian_id' => ['required', 'integer'],
            'relationship' => ['required', 'string', 'max:32'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_emergency' => ['sometimes', 'boolean'],
            'can_pickup' => ['sometimes', 'boolean'],
        ]);
        $guardian = Guardian::query()->findOrFail($data['guardian_id']);
        $this->assertTenantOwned($guardian);

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'tenant_id' => $student->tenant_id,
                'relationship' => $data['relationship'],
                'is_primary' => $request->boolean('is_primary'),
                'is_emergency' => $request->boolean('is_emergency'),
                'can_pickup' => $request->boolean('can_pickup', true),
            ],
        ]);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.guardian_linked'));
    }
}

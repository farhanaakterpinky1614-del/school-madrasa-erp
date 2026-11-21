<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Students\Models\Student;
use Modules\Students\Services\StudentIdGenerator;
use Modules\Students\Services\WithdrawalService;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with('currentEnrollment.schoolClass', 'currentEnrollment.section')
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('student_code', 'like', "%{$term}%")
                        ->orWhere('admission_number', 'like', "%{$term}%");
                });
            })
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.students.index', compact('students'));
    }

    public function create(): View
    {
        return view('tenant.students.create');
    }

    public function store(Request $request, StudentIdGenerator $ids): RedirectResponse
    {
        $data = $this->validated($request);
        $data['student_code'] = $ids->nextStudentCode();
        $data['admission_number'] = $data['admission_number'] ?: $ids->nextAdmissionNumber();
        $data['status'] = $data['status'] ?? Student::STATUS_ACTIVE;
        $data['enrolled_on'] = $data['enrolled_on'] ?? now()->toDateString();

        $student = Student::query()->create($data);
        $student->update(['qr_payload' => $ids->qrPayload($student)]);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.student_saved'));
    }

    public function show(Student $student): View
    {
        $this->assertTenantOwned($student);
        $student->load([
            'guardians',
            'documents',
            'enrollments.academicYear',
            'enrollments.schoolClass',
            'enrollments.section',
            'transfers',
            'promotions',
            'currentEnrollment.schoolClass',
            'currentEnrollment.section',
        ]);

        return view('tenant.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->assertTenantOwned($student);

        return view('tenant.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $this->validated($request, $student);
        $student->update($data);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.student_saved'));
    }

    public function withdraw(Request $request, Student $student, WithdrawalService $withdrawals): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'withdrawn_on' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'mark_alumni' => ['sometimes', 'boolean'],
        ]);
        $data['mark_alumni'] = $request->boolean('mark_alumni');
        $withdrawals->withdraw($student, $data);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.student_withdrawn'));
    }

    public function markAlumni(Student $student, WithdrawalService $withdrawals): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $withdrawals->markAlumni($student);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.student_alumni'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Student $student = null): array
    {
        $data = $request->validate([
            'admission_number' => ['nullable', 'string', 'max:64'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'religion' => ['nullable', 'string', 'max:64'],
            'nationality' => ['nullable', 'string', 'max:64'],
            'birth_certificate_no' => ['nullable', 'string', 'max:64'],
            'nid_no' => ['nullable', 'string', 'max:64'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,withdrawn,alumni,transferred'],
            'enrolled_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (array_key_exists('admission_number', $data)) {
            $data['admission_number'] = $data['admission_number'] ?: null;
        }

        return $data;
    }
}

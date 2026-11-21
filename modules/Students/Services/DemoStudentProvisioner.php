<?php

namespace Modules\Students\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Admissions\Models\AdmissionApplication;
use Modules\Admissions\Models\AdmissionFormField;
use Modules\Admissions\Models\AdmissionFormTemplate;
use Modules\Admissions\Models\AdmissionSession;
use Modules\Students\Models\Guardian;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo students, guardians, admission form/session/applications for a tenant.
 */
class DemoStudentProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected StudentIdGenerator $ids,
        protected EnrollmentService $enrollments,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->first();
            $class = SchoolClass::query()->orderBy('numeric_level')->first();
            $section = $class
                ? Section::query()->where('school_class_id', $class->id)->orderBy('name')->first()
                : null;

            $template = AdmissionFormTemplate::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'DEFAULT'],
                [
                    'name' => 'Default Admission Form',
                    'description' => 'Configurable foundation fields for online/offline intake.',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $fields = [
                ['field_key' => 'previous_school', 'label' => 'Previous school', 'field_type' => 'text', 'sort_order' => 1],
                ['field_key' => 'blood_group', 'label' => 'Blood group', 'field_type' => 'select', 'options' => ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'], 'sort_order' => 2],
                ['field_key' => 'medical_notes', 'label' => 'Medical notes', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 3],
            ];
            foreach ($fields as $field) {
                AdmissionFormField::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'admission_form_template_id' => $template->id,
                        'field_key' => $field['field_key'],
                    ],
                    [
                        'label' => $field['label'],
                        'field_type' => $field['field_type'],
                        'options' => $field['options'] ?? null,
                        'is_required' => $field['is_required'] ?? false,
                        'is_visible' => true,
                        'sort_order' => $field['sort_order'],
                        'applies_to' => 'both',
                    ]
                );
            }

            $session = AdmissionSession::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => '2025-INTAKE'],
                [
                    'academic_year_id' => $year?->id,
                    'school_class_id' => $class?->id,
                    'admission_form_template_id' => $template->id,
                    'name' => '2025 General Intake',
                    'public_slug' => 'demo-2025-intake',
                    'opens_at' => now()->subDays(30),
                    'closes_at' => now()->addDays(60),
                    'capacity' => 120,
                    'allow_online' => true,
                    'allow_offline' => true,
                    'is_active' => true,
                ]
            );

            $demoStudents = [
                ['first_name' => 'Ayesha', 'last_name' => 'Rahman', 'gender' => 'female', 'guardian' => 'Karim Rahman', 'relation' => 'father'],
                ['first_name' => 'Rahim', 'last_name' => 'Uddin', 'gender' => 'male', 'guardian' => 'Salma Begum', 'relation' => 'mother'],
                ['first_name' => 'Nusrat', 'last_name' => 'Jahan', 'gender' => 'female', 'guardian' => 'Imran Jahan', 'relation' => 'father'],
            ];

            foreach ($demoStudents as $i => $row) {
                $exists = Student::query()->where('first_name', $row['first_name'])->where('last_name', $row['last_name'])->exists();
                if ($exists) {
                    continue;
                }

                $student = Student::query()->create([
                    'student_code' => $this->ids->nextStudentCode(),
                    'admission_number' => $this->ids->nextAdmissionNumber(),
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'gender' => $row['gender'],
                    'date_of_birth' => now()->subYears(12 + $i)->subMonths($i)->toDateString(),
                    'phone' => '0171'.str_pad((string) (100000 + $i), 6, '0', STR_PAD_LEFT),
                    'city' => 'Dhaka',
                    'division' => 'Dhaka',
                    'status' => Student::STATUS_ACTIVE,
                    'enrolled_on' => now()->subMonths(2)->toDateString(),
                    'nationality' => 'Bangladeshi',
                ]);
                $student->update(['qr_payload' => $this->ids->qrPayload($student)]);

                $guardian = Guardian::query()->create([
                    'name' => $row['guardian'],
                    'phone' => '0181'.str_pad((string) (200000 + $i), 6, '0', STR_PAD_LEFT),
                    'address' => 'Demo Road, Dhaka',
                ]);
                $student->guardians()->attach($guardian->id, [
                    'tenant_id' => $tenant->id,
                    'relationship' => $row['relation'],
                    'is_primary' => true,
                    'is_emergency' => true,
                    'can_pickup' => true,
                ]);

                if ($year && $class) {
                    $this->enrollments->enroll($student, [
                        'academic_year_id' => $year->id,
                        'school_class_id' => $class->id,
                        'section_id' => $section?->id,
                        'roll_number' => (string) (10 + $i),
                        'enrolled_on' => now()->subMonths(2)->toDateString(),
                    ]);
                }
            }

            // Pending online application for workflow demo
            if ($session->id && ! AdmissionApplication::query()->where('application_number', 'APP-DEMO-0001')->exists()) {
                AdmissionApplication::query()->create([
                    'admission_session_id' => $session->id,
                    'application_number' => 'APP-DEMO-0001',
                    'channel' => 'online',
                    'status' => AdmissionApplication::STATUS_SUBMITTED,
                    'first_name' => 'Farhan',
                    'last_name' => 'Ahmed',
                    'gender' => 'male',
                    'date_of_birth' => now()->subYears(11)->toDateString(),
                    'phone' => '01910000001',
                    'guardian_name' => 'Jamal Ahmed',
                    'guardian_phone' => '01910000002',
                    'guardian_relation' => 'father',
                    'school_class_id' => $class?->id,
                    'form_payload' => ['previous_school' => 'Demo Primary', 'blood_group' => 'B+'],
                    'submitted_at' => now()->subDays(2),
                ]);
            }

            if ($session->id && ! AdmissionApplication::query()->where('application_number', 'APP-DEMO-0002')->exists()) {
                AdmissionApplication::query()->create([
                    'admission_session_id' => $session->id,
                    'application_number' => 'APP-DEMO-0002',
                    'channel' => 'offline',
                    'status' => AdmissionApplication::STATUS_APPROVED,
                    'first_name' => 'Maliha',
                    'last_name' => 'Sultana',
                    'gender' => 'female',
                    'date_of_birth' => now()->subYears(10)->toDateString(),
                    'phone' => '01910000003',
                    'guardian_name' => 'Rafiq Sultana',
                    'guardian_phone' => '01910000004',
                    'guardian_relation' => 'father',
                    'school_class_id' => $class?->id,
                    'form_payload' => ['previous_school' => 'City Kinder', 'blood_group' => 'O+'],
                    'submitted_at' => now()->subDays(5),
                    'reviewed_at' => now()->subDay(),
                    'decision_notes' => 'Approved for demo enrollment.',
                ]);
            }

        });
    }
}

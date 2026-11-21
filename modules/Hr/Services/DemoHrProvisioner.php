<?php

namespace Modules\Hr\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\Department;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Subject;
use Modules\Hr\Models\Designation;
use Modules\Hr\Models\LeaveType;
use Modules\Hr\Models\SalaryStructure;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\StaffSalary;
use Modules\Hr\Models\TeacherAssignment;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo designations, staff/teachers, leave types, salary & assignments.
 */
class DemoHrProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected StaffIdGenerator $ids,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $branch = Branch::query()->orderByDesc('is_primary')->first();
            $department = Department::query()->orderBy('name')->first();
            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->first();
            $class = SchoolClass::query()->orderBy('numeric_level')->first();
            $section = $class
                ? Section::query()->where('school_class_id', $class->id)->orderBy('name')->first()
                : null;
            $subject = Subject::query()->orderBy('name')->first();

            $teacherDesig = Designation::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'TCH'],
                ['name' => 'Teacher', 'description' => 'Classroom teacher / Ustaz', 'is_active' => true]
            );
            $hrDesig = Designation::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'HR'],
                ['name' => 'HR Officer', 'description' => 'Human resources', 'is_active' => true]
            );
            $acctDesig = Designation::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'ACCT'],
                ['name' => 'Accountant', 'description' => 'Accounts & payroll', 'is_active' => true]
            );

            foreach ([
                ['name' => 'Casual Leave', 'code' => 'CL', 'days_per_year' => 10, 'is_paid' => true],
                ['name' => 'Sick Leave', 'code' => 'SL', 'days_per_year' => 14, 'is_paid' => true],
                ['name' => 'Unpaid Leave', 'code' => 'UL', 'days_per_year' => 30, 'is_paid' => false],
            ] as $lt) {
                LeaveType::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $lt['code']],
                    [
                        'name' => $lt['name'],
                        'days_per_year' => $lt['days_per_year'],
                        'is_paid' => $lt['is_paid'],
                        'requires_approval' => true,
                        'is_active' => true,
                    ]
                );
            }

            $teacherSalary = SalaryStructure::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'TCH-STD'],
                [
                    'name' => 'Standard Teacher',
                    'basic_salary' => 25000,
                    'house_rent' => 5000,
                    'medical' => 1500,
                    'conveyance' => 1000,
                    'other_allowance' => 500,
                    'deductions' => 500,
                    'is_active' => true,
                ]
            );
            $staffSalary = SalaryStructure::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'STF-STD'],
                [
                    'name' => 'Standard Staff',
                    'basic_salary' => 22000,
                    'house_rent' => 4000,
                    'medical' => 1200,
                    'conveyance' => 800,
                    'other_allowance' => 0,
                    'deductions' => 400,
                    'is_active' => true,
                ]
            );

            $demoStaff = [
                [
                    'first_name' => 'Fatima', 'last_name' => 'Begum', 'staff_type' => Staff::TYPE_TEACHER,
                    'is_teacher' => true, 'designation_id' => $teacherDesig->id, 'salary' => $teacherSalary,
                    'email' => 'fatima.teacher@demo-school.test', 'phone' => '01711111111',
                ],
                [
                    'first_name' => 'Karim', 'last_name' => 'Hossain', 'staff_type' => Staff::TYPE_TEACHER,
                    'is_teacher' => true, 'designation_id' => $teacherDesig->id, 'salary' => $teacherSalary,
                    'email' => 'karim.teacher@demo-school.test', 'phone' => '01722222222',
                ],
                [
                    'first_name' => 'Nadia', 'last_name' => 'Islam', 'staff_type' => Staff::TYPE_EMPLOYEE,
                    'is_teacher' => false, 'designation_id' => $hrDesig->id, 'salary' => $staffSalary,
                    'email' => 'nadia.hr@demo-school.test', 'phone' => '01733333333',
                ],
                [
                    'first_name' => 'Rafiq', 'last_name' => 'Ahmed', 'staff_type' => Staff::TYPE_EMPLOYEE,
                    'is_teacher' => false, 'designation_id' => $acctDesig->id, 'salary' => $staffSalary,
                    'email' => 'rafiq.accounts@demo-school.test', 'phone' => '01744444444',
                ],
            ];

            $teachers = [];
            foreach ($demoStaff as $row) {
                $staff = Staff::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'email' => $row['email'],
                    ],
                    [
                        'employee_code' => Staff::query()->where('email', $row['email'])->value('employee_code')
                            ?: $this->ids->nextEmployeeCode(),
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'phone' => $row['phone'],
                        'gender' => 'other',
                        'staff_type' => $row['staff_type'],
                        'is_teacher' => $row['is_teacher'],
                        'designation_id' => $row['designation_id'],
                        'department_id' => $department?->id,
                        'branch_id' => $branch?->id,
                        'joined_on' => now()->subMonths(6)->toDateString(),
                        'status' => Staff::STATUS_ACTIVE,
                        'city' => 'Dhaka',
                        'division' => 'Dhaka',
                    ]
                );

                StaffSalary::query()->where('staff_id', $staff->id)->update(['is_current' => false]);
                StaffSalary::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'staff_id' => $staff->id,
                        'salary_structure_id' => $row['salary']->id,
                        'effective_from' => now()->startOfYear()->toDateString(),
                    ],
                    ['is_current' => true]
                );

                if ($staff->is_teacher) {
                    $teachers[] = $staff;
                }
            }

            if ($teachers !== [] && $class) {
                TeacherAssignment::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'staff_id' => $teachers[0]->id,
                        'school_class_id' => $class->id,
                        'section_id' => $section?->id,
                        'subject_id' => $subject?->id,
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'is_class_teacher' => true,
                        'is_active' => true,
                    ]
                );

                if (isset($teachers[1]) && $subject) {
                    TeacherAssignment::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'staff_id' => $teachers[1]->id,
                            'school_class_id' => $class->id,
                            'section_id' => $section?->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'academic_year_id' => $year?->id,
                            'is_class_teacher' => false,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}

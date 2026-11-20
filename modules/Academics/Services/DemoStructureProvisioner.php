<?php

namespace Modules\Academics\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\CalendarEvent;
use Modules\Academics\Models\Curriculum;
use Modules\Academics\Models\CurriculumSubject;
use Modules\Academics\Models\Department;
use Modules\Academics\Models\GradingScale;
use Modules\Academics\Models\GradingScaleBand;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;
use Modules\Academics\Models\Subject;
use Modules\Settings\Services\InstitutionSettingsService;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds a sensible academic structure for one tenant (demo / onboarding foundation).
 */
class DemoStructureProvisioner
{
    public function __construct(
        protected InstitutionTypePreset $presets,
        protected InstitutionSettingsService $settings,
        protected CurrentTenant $currentTenant,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $preset = $this->presets->for($tenant->institution_type);

            $this->settings->putMany($tenant, [
                'locale' => $tenant->locale_default ?: 'en',
                'currency' => $tenant->currency ?: 'BDT',
                'timezone' => $tenant->timezone ?: 'Asia/Dhaka',
                'working_days' => ['sun', 'mon', 'tue', 'wed', 'thu'],
                'terminology' => $preset['terminology'] ?? [],
                'branding' => [
                    'primary_color' => '#4F46E5',
                    'secondary_color' => '#0F172A',
                ],
            ]);

            $branch = Branch::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'MAIN'],
                [
                    'name' => 'Main Campus',
                    'city' => 'Dhaka',
                    'division' => 'Dhaka',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            $department = null;
            if (! empty($preset['features']['departments'])) {
                $department = Department::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => 'GEN'],
                    [
                        'branch_id' => $branch->id,
                        'name' => ($preset['terminology']['department'] ?? 'Department').' — General',
                        'kind' => 'academic',
                        'is_active' => true,
                    ]
                );
            } else {
                Department::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => 'ADMIN'],
                    [
                        'branch_id' => $branch->id,
                        'name' => 'Administration',
                        'kind' => 'administrative',
                        'is_active' => true,
                    ]
                );
            }

            $year = AcademicYear::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => '2025-26'],
                [
                    'name' => '2025–2026',
                    'starts_on' => '2025-01-01',
                    'ends_on' => '2025-12-31',
                    'is_current' => true,
                    'is_active' => true,
                ]
            );

            foreach ([
                ['name' => 'First Term', 'code' => 'T1', 'sort_order' => 1, 'is_current' => true],
                ['name' => 'Second Term', 'code' => 'T2', 'sort_order' => 2, 'is_current' => false],
                ['name' => 'Final Term', 'code' => 'T3', 'sort_order' => 3, 'is_current' => false],
            ] as $term) {
                AcademicTerm::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'academic_year_id' => $year->id,
                        'code' => $term['code'],
                    ],
                    [
                        'name' => $term['name'],
                        'sort_order' => $term['sort_order'],
                        'is_current' => $term['is_current'],
                        'is_active' => true,
                        'starts_on' => $year->starts_on,
                        'ends_on' => $year->ends_on,
                    ]
                );
            }

            $morning = Shift::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'MOR'],
                ['name' => 'Morning', 'starts_at' => '08:00:00', 'ends_at' => '13:00:00', 'is_active' => true]
            );

            Shift::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'DAY'],
                ['name' => 'Day', 'starts_at' => '13:30:00', 'ends_at' => '17:30:00', 'is_active' => true]
            );

            $classes = [];
            $levels = $preset['default_classes'] ?? [6, 7, 8, 9, 10];
            foreach ($levels as $i => $level) {
                $label = is_int($level)
                    ? ($preset['terminology']['class'] ?? 'Class').' '.$level
                    : (string) $level;
                $code = is_int($level) ? 'C'.$level : 'C'.($i + 1);

                $classes[$code] = SchoolClass::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    [
                        'department_id' => $department?->id,
                        'name' => $label,
                        'numeric_level' => is_int($level) ? $level : null,
                        'sort_order' => $i + 1,
                        'is_active' => true,
                    ]
                );

                foreach (['A', 'B'] as $sec) {
                    Section::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'school_class_id' => $classes[$code]->id,
                            'code' => $sec,
                        ],
                        [
                            'name' => 'Section '.$sec,
                            'shift_id' => $morning->id,
                            'capacity' => 40,
                            'is_active' => true,
                        ]
                    );
                }
            }

            $subjectDefs = [
                ['name' => 'Bangla', 'code' => 'BAN', 'type' => 'general'],
                ['name' => 'English', 'code' => 'ENG', 'type' => 'general'],
                ['name' => 'Mathematics', 'code' => 'MATH', 'type' => 'general'],
                ['name' => 'Science', 'code' => 'SCI', 'type' => 'general'],
                ['name' => 'Islamic Studies', 'code' => 'IS', 'type' => 'islamic'],
                ['name' => 'Quran', 'code' => 'QUR', 'type' => 'islamic'],
            ];

            if (! empty($preset['features']['hifz'])) {
                $subjectDefs[] = ['name' => 'Hifz Progress', 'code' => 'HIFZ', 'type' => 'hifz'];
            }

            $subjects = [];
            foreach ($subjectDefs as $def) {
                if ($def['type'] === 'general' && empty($preset['features']['standard_subjects'])) {
                    continue;
                }
                $subjects[$def['code']] = Subject::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $def['code']],
                    [
                        'name' => $def['name'],
                        'subject_type' => $def['type'],
                        'is_active' => true,
                    ]
                );
            }

            $curriculum = Curriculum::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'DEFAULT'],
                [
                    'name' => ($preset['label'] ?? 'School').' Curriculum',
                    'board' => $tenant->institution?->board,
                    'description' => 'Configurable default curriculum (not hardcoded runtime exams).',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $firstClass = reset($classes) ?: null;
            foreach ($subjects as $subject) {
                CurriculumSubject::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'curriculum_id' => $curriculum->id,
                        'subject_id' => $subject->id,
                        'school_class_id' => $firstClass?->id,
                    ],
                    [
                        'is_compulsory' => true,
                        'weekly_periods' => 4,
                    ]
                );
            }

            $scale = GradingScale::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'BD-GPA'],
                [
                    'name' => 'Bangladesh GPA Scale',
                    'description' => 'Configurable grading bands (exam runtime is later phase).',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $bands = [
                ['grade' => 'A+', 'min' => 80, 'max' => 100, 'gpa' => 5.00, 'order' => 1],
                ['grade' => 'A', 'min' => 70, 'max' => 79.99, 'gpa' => 4.00, 'order' => 2],
                ['grade' => 'A-', 'min' => 60, 'max' => 69.99, 'gpa' => 3.50, 'order' => 3],
                ['grade' => 'B', 'min' => 50, 'max' => 59.99, 'gpa' => 3.00, 'order' => 4],
                ['grade' => 'C', 'min' => 40, 'max' => 49.99, 'gpa' => 2.00, 'order' => 5],
                ['grade' => 'D', 'min' => 33, 'max' => 39.99, 'gpa' => 1.00, 'order' => 6],
                ['grade' => 'F', 'min' => 0, 'max' => 32.99, 'gpa' => 0.00, 'order' => 7],
            ];

            foreach ($bands as $band) {
                GradingScaleBand::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'grading_scale_id' => $scale->id,
                        'grade' => $band['grade'],
                    ],
                    [
                        'min_mark' => $band['min'],
                        'max_mark' => $band['max'],
                        'gpa' => $band['gpa'],
                        'sort_order' => $band['order'],
                        'remark' => null,
                    ]
                );
            }

            CalendarEvent::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Independence Day',
                    'starts_on' => '2025-03-26',
                ],
                [
                    'academic_year_id' => $year->id,
                    'event_type' => 'holiday',
                    'ends_on' => '2025-03-26',
                    'is_recurring' => true,
                    'description' => 'National holiday (foundation calendar).',
                    'is_active' => true,
                ]
            );

            CalendarEvent::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Victory Day',
                    'starts_on' => '2025-12-16',
                ],
                [
                    'academic_year_id' => $year->id,
                    'event_type' => 'holiday',
                    'ends_on' => '2025-12-16',
                    'is_recurring' => true,
                    'is_active' => true,
                ]
            );
        });
    }
}

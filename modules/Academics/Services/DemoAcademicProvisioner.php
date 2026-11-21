<?php

namespace Modules\Academics\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\ClassworkAssignment;
use Modules\Academics\Models\Curriculum;
use Modules\Academics\Models\HomeworkItem;
use Modules\Academics\Models\LearningMaterial;
use Modules\Academics\Models\LessonPlan;
use Modules\Academics\Models\Period;
use Modules\Academics\Models\Room;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;
use Modules\Academics\Models\Subject;
use Modules\Academics\Models\Syllabus;
use Modules\Academics\Models\SyllabusTopic;
use Modules\Hr\Models\Staff;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo rooms, periods, routines, syllabus, lesson plans, homework, classwork, materials.
 */
class DemoAcademicProvisioner
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $branch = Branch::query()->orderByDesc('is_primary')->first();
            $shift = Shift::query()->orderBy('name')->first();
            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->first();
            $class = SchoolClass::query()->orderBy('numeric_level')->first();
            $section = $class
                ? Section::query()->where('school_class_id', $class->id)->orderBy('name')->first()
                : null;
            $subject = Subject::query()->orderBy('name')->first();
            $curriculum = Curriculum::query()->orderByDesc('is_default')->first();
            $teacher = Staff::query()->teachers()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->first();

            $room = Room::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'R101'],
                [
                    'branch_id' => $branch?->id,
                    'name' => 'Room 101',
                    'room_type' => 'classroom',
                    'capacity' => 40,
                    'building' => 'Main',
                    'floor' => '1',
                    'is_active' => true,
                ]
            );
            Room::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'LAB1'],
                [
                    'branch_id' => $branch?->id,
                    'name' => 'Science Lab',
                    'room_type' => 'lab',
                    'capacity' => 30,
                    'building' => 'Main',
                    'floor' => '2',
                    'is_active' => true,
                ]
            );

            $periods = [];
            foreach ([
                ['name' => 'Period 1', 'code' => 'P1', 'sort_order' => 1, 'starts_at' => '08:00', 'ends_at' => '08:45', 'is_break' => false],
                ['name' => 'Period 2', 'code' => 'P2', 'sort_order' => 2, 'starts_at' => '08:45', 'ends_at' => '09:30', 'is_break' => false],
                ['name' => 'Tiffin', 'code' => 'BRK', 'sort_order' => 3, 'starts_at' => '09:30', 'ends_at' => '09:50', 'is_break' => true],
                ['name' => 'Period 3', 'code' => 'P3', 'sort_order' => 4, 'starts_at' => '09:50', 'ends_at' => '10:35', 'is_break' => false],
                ['name' => 'Period 4', 'code' => 'P4', 'sort_order' => 5, 'starts_at' => '10:35', 'ends_at' => '11:20', 'is_break' => false],
            ] as $def) {
                $periods[$def['code']] = Period::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $def['code']],
                    [
                        'shift_id' => $shift?->id,
                        'name' => $def['name'],
                        'sort_order' => $def['sort_order'],
                        'starts_at' => $def['starts_at'],
                        'ends_at' => $def['ends_at'],
                        'is_break' => $def['is_break'],
                        'is_active' => true,
                    ]
                );
            }

            if ($class && $subject && isset($periods['P1'])) {
                // Sunday = 0 … Saturday = 6 — seed Sun–Thu teaching slots
                foreach ([0, 1, 2, 3, 4] as $day) {
                    $period = $day % 2 === 0 ? $periods['P1'] : $periods['P2'];
                    ClassRoutine::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'academic_year_id' => $year?->id,
                            'school_class_id' => $class->id,
                            'section_id' => $section?->id,
                            'day_of_week' => $day,
                            'period_id' => $period->id,
                        ],
                        [
                            'subject_id' => $subject->id,
                            'staff_id' => $teacher?->id,
                            'room_id' => $room->id,
                            'is_active' => true,
                        ]
                    );
                }
            }

            if ($class && $subject) {
                $syllabus = Syllabus::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'academic_year_id' => $year?->id,
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'curriculum_id' => $curriculum?->id,
                        'title' => $subject->name.' syllabus — '.$class->name,
                        'description' => 'Demo syllabus outline for the current session.',
                        'is_active' => true,
                    ]
                );

                $topic = SyllabusTopic::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'syllabus_id' => $syllabus->id,
                        'title' => 'Unit 1 — Foundations',
                    ],
                    [
                        'description' => 'Introductory concepts and practice.',
                        'sort_order' => 1,
                        'estimated_periods' => 8,
                        'is_active' => true,
                    ]
                );
                SyllabusTopic::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'syllabus_id' => $syllabus->id,
                        'title' => 'Unit 2 — Application',
                    ],
                    [
                        'description' => 'Applied exercises and assessment prep.',
                        'sort_order' => 2,
                        'estimated_periods' => 6,
                        'is_active' => true,
                    ]
                );

                LessonPlan::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'title' => 'Lesson 1 — Introduction',
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'section_id' => $section?->id,
                        'staff_id' => $teacher?->id,
                        'syllabus_topic_id' => $topic->id,
                        'objectives' => 'Introduce core ideas; check prior knowledge.',
                        'activities' => 'Warm-up discussion, board work, pair practice.',
                        'resources' => 'Textbook ch.1, worksheet A.',
                        'homework_notes' => 'Read pages 1–5.',
                        'planned_on' => now()->toDateString(),
                        'status' => LessonPlan::STATUS_PUBLISHED,
                        'is_active' => true,
                    ]
                );

                HomeworkItem::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'title' => 'Homework — Unit 1 practice',
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'section_id' => $section?->id,
                        'staff_id' => $teacher?->id,
                        'description' => 'Complete exercises 1–10 from the workbook.',
                        'assigned_on' => now()->toDateString(),
                        'due_on' => now()->addDays(3)->toDateString(),
                        'status' => HomeworkItem::STATUS_PUBLISHED,
                    ]
                );

                ClassworkAssignment::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'title' => 'Assignment — Short essay',
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'section_id' => $section?->id,
                        'staff_id' => $teacher?->id,
                        'description' => 'Write a one-page summary of Unit 1.',
                        'assigned_on' => now()->toDateString(),
                        'due_on' => now()->addDays(7)->toDateString(),
                        'max_marks' => 20,
                        'status' => ClassworkAssignment::STATUS_PUBLISHED,
                    ]
                );

                LearningMaterial::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'title' => 'Unit 1 reading list',
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'staff_id' => $teacher?->id,
                        'description' => 'Recommended readings and links for Unit 1.',
                        'material_type' => LearningMaterial::TYPE_NOTE,
                        'is_published' => true,
                    ]
                );
                LearningMaterial::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'title' => 'NCTB curriculum portal',
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'academic_year_id' => $year?->id,
                        'staff_id' => $teacher?->id,
                        'description' => 'External curriculum reference.',
                        'material_type' => LearningMaterial::TYPE_LINK,
                        'external_url' => 'https://nctb.gov.bd',
                        'is_published' => true,
                    ]
                );
            }
        });
    }
}

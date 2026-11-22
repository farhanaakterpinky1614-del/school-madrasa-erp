<?php

namespace Modules\Examinations\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\GradingScale;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Subject;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;
use Modules\Examinations\Models\GradingScheme;
use Modules\Examinations\Models\Mark;
use Modules\Examinations\Models\ResultPublication;
use Modules\Students\Models\StudentEnrollment;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo exam, papers, draft/published marks, and grading scheme wrap.
 */
class DemoExamProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected GradeResolver $grades,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $year = AcademicYear::query()->where('is_current', true)->first()
                ?? AcademicYear::query()->first();
            $term = AcademicTerm::query()->orderBy('id')->first();
            $class = SchoolClass::query()->orderBy('numeric_level')->first();
            $section = $class
                ? Section::query()->where('school_class_id', $class->id)->orderBy('name')->first()
                : null;
            $scale = GradingScale::query()->where('is_default', true)->first()
                ?? GradingScale::query()->first();

            if (! $year || ! $class || ! $scale) {
                return;
            }

            $scheme = GradingScheme::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'BD-GPA',
                ],
                [
                    'name' => 'Bangladesh GPA (wrap)',
                    'grading_scale_id' => $scale->id,
                    'description' => 'Wraps Phase 04 grading scale bands for exam letter grades.',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $exam = Exam::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'HALF-2026',
                ],
                [
                    'name' => 'Half-Yearly Examination 2026',
                    'academic_year_id' => $year->id,
                    'academic_term_id' => $term?->id,
                    'school_class_id' => $class->id,
                    'grading_scheme_id' => $scheme->id,
                    'starts_on' => now('Asia/Dhaka')->subDays(14)->toDateString(),
                    'ends_on' => now('Asia/Dhaka')->subDays(7)->toDateString(),
                    'status' => Exam::STATUS_ONGOING,
                    'board_name' => 'Dhaka',
                    'board_roll_prefix' => 'DHK-DEMO-',
                    'board_meta' => [
                        'center_code' => 'DEMO-01',
                        'session' => '2026',
                    ],
                    'notes' => 'Demo half-yearly exam for Try Live.',
                ]
            );

            $subjects = Subject::query()->orderBy('id')->limit(4)->get();
            if ($subjects->isEmpty()) {
                return;
            }

            $papers = [];
            foreach ($subjects as $i => $subject) {
                $papers[] = ExamSubject::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'exam_id' => $exam->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'full_marks' => 100,
                        'pass_marks' => 33,
                        'weight' => 1,
                        'sort_order' => $i + 1,
                        'is_optional' => false,
                    ]
                );
            }

            $enrollments = StudentEnrollment::query()
                ->where('school_class_id', $class->id)
                ->when($section, fn ($q) => $q->where('section_id', $section->id))
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->with('student')
                ->orderBy('roll_number')
                ->limit(8)
                ->get();

            $sampleScores = [92, 78, 65, 55, 48, 33, 22, null];

            foreach ($papers as $paperIndex => $paper) {
                foreach ($enrollments as $i => $enrollment) {
                    $student = $enrollment->student;
                    if (! $student) {
                        continue;
                    }

                    $score = $sampleScores[$i % count($sampleScores)];
                    $isAbsent = $score === null && $paperIndex === 0 && $i === 7;
                    if ($score === null && ! $isAbsent) {
                        $score = 70 - ($i * 5);
                    }

                    $resolved = $this->grades->resolve(
                        $exam->loadMissing('gradingScheme.gradingScale.bands'),
                        $paper,
                        $isAbsent ? null : (float) $score,
                        $isAbsent
                    );

                    Mark::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'exam_subject_id' => $paper->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'exam_id' => $exam->id,
                            'section_id' => $section?->id ?? $enrollment->section_id,
                            'marks_obtained' => $isAbsent ? null : $score,
                            'grade' => $resolved['grade'],
                            'gpa' => $resolved['gpa'],
                            'is_absent' => $isAbsent,
                            'entry_status' => Mark::ENTRY_SUBMITTED,
                            'remarks' => $isAbsent ? 'Demo absent' : null,
                        ]
                    );
                }
            }

            // Second draft-only exam for publish workflow demos
            $draftExam = Exam::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'FINAL-DRAFT',
                ],
                [
                    'name' => 'Annual Final (Draft)',
                    'academic_year_id' => $year->id,
                    'academic_term_id' => $term?->id,
                    'school_class_id' => $class->id,
                    'grading_scheme_id' => $scheme->id,
                    'starts_on' => now('Asia/Dhaka')->addDays(30)->toDateString(),
                    'ends_on' => now('Asia/Dhaka')->addDays(40)->toDateString(),
                    'status' => Exam::STATUS_DRAFT,
                    'board_name' => null,
                    'board_roll_prefix' => null,
                    'notes' => 'Unpublished draft exam — results hidden from parent/student views.',
                ]
            );

            $firstPaper = $papers[0] ?? null;
            if ($firstPaper) {
                ExamSubject::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'exam_id' => $draftExam->id,
                        'subject_id' => $firstPaper->subject_id,
                    ],
                    [
                        'full_marks' => 100,
                        'pass_marks' => 33,
                        'weight' => 1,
                        'sort_order' => 1,
                    ]
                );
            }

            ResultPublication::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'exam_id' => $exam->id,
                    'section_id' => null,
                ],
                [
                    'published_at' => now('Asia/Dhaka')->subDay(),
                    'notes' => 'Demo published results for Half-Yearly.',
                ]
            );
        });
    }
}

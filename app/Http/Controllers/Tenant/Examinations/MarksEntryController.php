<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Examinations\Concerns\LoadsExamFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;
use Modules\Examinations\Models\Mark;
use Modules\Examinations\Services\GradeResolver;
use Modules\Examinations\Services\TeacherExamScope;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class MarksEntryController extends Controller
{
    use LoadsExamFormOptions;

    public function index(Request $request, TeacherExamScope $scope): View
    {
        $options = $this->examFormOptions($request->user());

        $examsQuery = Exam::query()
            ->with('schoolClass')
            ->whereIn('status', [Exam::STATUS_SCHEDULED, Exam::STATUS_ONGOING, Exam::STATUS_CLOSED, Exam::STATUS_DRAFT])
            ->orderByDesc('starts_on');

        if ($options['scoped'] && $options['allowed']) {
            $examsQuery->whereIn('school_class_id', $options['allowed']['class_ids'] ?: [0]);
        }

        $exams = $examsQuery->get();
        $examId = $request->integer('exam_id') ?: $exams->first()?->id;
        $exam = $examId ? Exam::query()->with(['subjects.subject', 'gradingScheme.gradingScale.bands'])->find($examId) : null;

        if ($exam) {
            $this->assertTenantOwned($exam);
            abort_unless($scope->canAccessExam($request->user(), $exam), 403);
        }

        $papers = $exam?->subjects ?? collect();
        if ($options['scoped'] && $options['allowed'] && $exam) {
            $subjectIds = $options['allowed']['subject_ids'];
            if ($subjectIds !== []) {
                $papers = $papers->filter(
                    fn (ExamSubject $p) => in_array((int) $p->subject_id, $subjectIds, true)
                )->values();
            }
        }

        $paperId = $request->integer('exam_subject_id') ?: $papers->first()?->id;
        $paper = $paperId ? $papers->firstWhere('id', $paperId) : null;
        $sectionId = $request->filled('section_id') && $request->input('section_id') !== ''
            ? $request->integer('section_id')
            : null;

        $students = collect();
        $existing = collect();

        if ($exam && $paper) {
            $enrollments = StudentEnrollment::query()
                ->with('student')
                ->where('school_class_id', $exam->school_class_id)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->orderBy('roll_number')
                ->get();

            $students = $enrollments->map(function (StudentEnrollment $e) {
                $student = $e->student;
                if ($student) {
                    $student->setAttribute('roll_number', $e->roll_number);
                    $student->setAttribute('enrollment_section_id', $e->section_id);
                }

                return $student;
            })->filter()->values();

            $existing = Mark::query()
                ->where('exam_subject_id', $paper->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        $sections = ($exam
            ? $options['sections']->where('school_class_id', $exam->school_class_id)
            : $options['sections'])->values();

        return view('tenant.examinations.marks.index', array_merge($options, [
            'exams' => $exams,
            'exam' => $exam,
            'papers' => $papers,
            'paper' => $paper,
            'examId' => $examId,
            'paperId' => $paperId,
            'sectionId' => $sectionId,
            'sections' => $sections,
            'students' => $students,
            'existing' => $existing,
        ]));
    }

    public function store(Request $request, TeacherExamScope $scope, GradeResolver $grades): RedirectResponse
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', Rule::exists('exams', 'id')],
            'exam_subject_id' => ['required', 'integer', Rule::exists('exam_subjects', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'entries.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'entries.*.is_absent' => ['sometimes', 'boolean'],
            'entries.*.remarks' => ['nullable', 'string', 'max:255'],
            'entries.*.entry_status' => ['nullable', Rule::in(Mark::ENTRY_STATUSES)],
        ]);

        $exam = Exam::query()->with('gradingScheme.gradingScale.bands')->findOrFail($data['exam_id']);
        $this->assertTenantOwned($exam);

        $paper = ExamSubject::query()->findOrFail($data['exam_subject_id']);
        $this->assertTenantOwned($paper);
        abort_unless((int) $paper->exam_id === (int) $exam->id, 404);

        $sectionId = isset($data['section_id']) ? (int) $data['section_id'] : null;

        abort_unless(
            $scope->canEnterMarks($request->user(), $exam, $paper, $sectionId),
            403
        );

        foreach ($data['entries'] as $entry) {
            $student = Student::query()->findOrFail($entry['student_id']);
            $this->assertTenantOwned($student);

            $isAbsent = (bool) ($entry['is_absent'] ?? false);
            $marksObtained = $isAbsent ? null : (isset($entry['marks_obtained']) && $entry['marks_obtained'] !== ''
                ? (float) $entry['marks_obtained']
                : null);

            if ($marksObtained !== null && $marksObtained > (float) $paper->full_marks) {
                return back()->withErrors([
                    'entries' => __('ui.marks_exceed_full', ['max' => $paper->full_marks]),
                ])->withInput();
            }

            $resolved = $grades->resolve($exam, $paper, $marksObtained, $isAbsent);

            Mark::query()->updateOrCreate(
                [
                    'exam_subject_id' => $paper->id,
                    'student_id' => $student->id,
                ],
                [
                    'exam_id' => $exam->id,
                    'section_id' => $sectionId,
                    'marks_obtained' => $marksObtained,
                    'grade' => $resolved['grade'],
                    'gpa' => $resolved['gpa'],
                    'is_absent' => $isAbsent,
                    'remarks' => $entry['remarks'] ?? null,
                    'entry_status' => $entry['entry_status'] ?? Mark::ENTRY_DRAFT,
                    'entered_by' => $request->user()?->id,
                ]
            );
        }

        return redirect()
            ->route('tenant.marks.index', [
                'exam_id' => $exam->id,
                'exam_subject_id' => $paper->id,
                'section_id' => $sectionId,
            ])
            ->with('status', __('ui.marks_saved'));
    }
}

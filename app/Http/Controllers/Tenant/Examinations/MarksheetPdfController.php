<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Examinations\Concerns\LoadsExamFormOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\Mark;
use Modules\Examinations\Models\ResultPublication;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;
use Modules\Tenancy\Models\Institution;

class MarksheetPdfController extends Controller
{
    use LoadsExamFormOptions;

    public function index(Request $request): View
    {
        $options = $this->examFormOptions($request->user());
        $exams = Exam::query()->with('schoolClass')->orderByDesc('starts_on')->get();
        $examId = $request->integer('exam_id') ?: $exams->first()?->id;
        $exam = $examId ? Exam::query()->find($examId) : null;

        $students = collect();
        if ($exam) {
            $this->assertTenantOwned($exam);
            $students = StudentEnrollment::query()
                ->with('student')
                ->where('school_class_id', $exam->school_class_id)
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->orderBy('roll_number')
                ->get();
        }

        return view('tenant.examinations.pdf.index', array_merge($options, [
            'exams' => $exams,
            'exam' => $exam,
            'examId' => $examId,
            'students' => $students,
        ]));
    }

    public function studentMarksheet(Request $request, Exam $exam, Student $student): Response
    {
        $this->assertTenantOwned($exam);
        $this->assertTenantOwned($student);

        $this->assertPublishedOrStaff($exam, $request);

        $exam->load(['schoolClass', 'academicYear', 'academicTerm', 'gradingScheme.gradingScale.bands', 'subjects.subject']);

        $marks = Mark::query()
            ->with('examSubject.subject')
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('exam_subject_id');

        $enrollment = StudentEnrollment::query()
            ->where('student_id', $student->id)
            ->where('school_class_id', $exam->school_class_id)
            ->where('is_current', true)
            ->first();

        $institution = Institution::query()->first();

        $rows = [];
        $totalObtained = 0.0;
        $totalFull = 0.0;
        $gpaSum = 0.0;
        $gpaCount = 0;

        foreach ($exam->subjects as $paper) {
            $mark = $marks->get($paper->id);
            $obtained = $mark && ! $mark->is_absent ? (float) $mark->marks_obtained : null;
            $rows[] = [
                'subject' => $paper->subject?->name ?? '—',
                'full_marks' => (float) $paper->full_marks,
                'pass_marks' => (float) $paper->pass_marks,
                'obtained' => $obtained,
                'grade' => $mark?->grade,
                'gpa' => $mark?->gpa,
                'absent' => (bool) ($mark?->is_absent),
            ];
            $totalFull += (float) $paper->full_marks;
            if ($obtained !== null) {
                $totalObtained += $obtained;
            }
            if ($mark?->gpa !== null && ! $mark->is_absent) {
                $gpaSum += (float) $mark->gpa;
                $gpaCount++;
            }
        }

        $pdf = Pdf::loadView('tenant.examinations.pdf.marksheet', [
            'exam' => $exam,
            'student' => $student,
            'enrollment' => $enrollment,
            'institution' => $institution,
            'rows' => $rows,
            'totalObtained' => $totalObtained,
            'totalFull' => $totalFull,
            'averageGpa' => $gpaCount > 0 ? round($gpaSum / $gpaCount, 2) : null,
            'fontNote' => true,
        ])->setPaper('a4');

        $filename = 'marksheet-'.$exam->id.'-'.$student->id.'.pdf';

        return $pdf->download($filename);
    }

    public function classSheet(Request $request, Exam $exam): Response
    {
        $this->assertTenantOwned($exam);
        $this->assertPublishedOrStaff($exam, $request);

        $exam->load(['schoolClass', 'academicYear', 'subjects.subject']);
        $sectionId = $request->filled('section_id') ? $request->integer('section_id') : null;

        $enrollments = StudentEnrollment::query()
            ->with('student')
            ->where('school_class_id', $exam->school_class_id)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->where('is_current', true)
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->orderBy('roll_number')
            ->get();

        $allMarks = Mark::query()
            ->where('exam_id', $exam->id)
            ->get()
            ->groupBy('student_id');

        $students = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            if (! $student) {
                continue;
            }
            $byPaper = ($allMarks->get($student->id) ?? collect())->keyBy('exam_subject_id');
            $cells = [];
            $sum = 0.0;
            $count = 0;
            foreach ($exam->subjects as $paper) {
                $m = $byPaper->get($paper->id);
                if ($m?->is_absent) {
                    $cells[] = 'Abs';
                } elseif ($m?->marks_obtained !== null) {
                    $cells[] = (string) $m->marks_obtained;
                    $sum += (float) $m->marks_obtained;
                    $count++;
                } else {
                    $cells[] = '—';
                }
            }
            $students[] = [
                'roll' => $enrollment->roll_number,
                'name' => $student->full_name,
                'code' => $student->student_code,
                'cells' => $cells,
                'total' => $count > 0 ? $sum : null,
            ];
        }

        $institution = Institution::query()->first();

        $pdf = Pdf::loadView('tenant.examinations.pdf.class-sheet', [
            'exam' => $exam,
            'institution' => $institution,
            'papers' => $exam->subjects,
            'students' => $students,
            'fontNote' => true,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('class-result-'.$exam->id.'.pdf');
    }

    private function assertPublishedOrStaff(Exam $exam, Request $request): void
    {
        $user = $request->user();
        if ($user && $user->can('exams.result.view')) {
            return;
        }

        $published = ResultPublication::query()
            ->where('exam_id', $exam->id)
            ->whereNotNull('published_at')
            ->exists();

        abort_unless($published, 403, 'Results are not published yet.');
    }
}

<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Examinations\Concerns\LoadsExamFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Services\TeacherExamScope;

class ExamController extends Controller
{
    use LoadsExamFormOptions;

    public function index(Request $request, TeacherExamScope $scope): View
    {
        $options = $this->examFormOptions($request->user());
        $query = Exam::query()
            ->with(['schoolClass', 'academicYear', 'academicTerm', 'gradingScheme'])
            ->withCount(['subjects', 'marks', 'publications'])
            ->orderByDesc('starts_on')
            ->orderBy('name');

        if ($options['scoped'] && $options['allowed']) {
            $query->whereIn('school_class_id', $options['allowed']['class_ids'] ?: [0]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->integer('class_id'));
        }

        return view('tenant.examinations.exams.index', array_merge($options, [
            'exams' => $query->paginate(20)->withQueryString(),
            'filterStatus' => $request->string('status')->toString(),
            'filterClassId' => $request->integer('class_id') ?: null,
        ]));
    }

    public function create(): View
    {
        return view('tenant.examinations.exams.create', $this->examFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->examPayload($request);
        $data['created_by'] = $request->user()?->id;

        $exam = Exam::query()->create($data);

        return redirect()
            ->route('tenant.exams.show', $exam)
            ->with('status', __('ui.exam_saved'));
    }

    public function show(Exam $exam, TeacherExamScope $scope): View
    {
        $this->assertTenantOwned($exam);
        abort_unless($scope->canAccessExam(request()->user(), $exam), 403);

        $exam->load([
            'schoolClass', 'academicYear', 'academicTerm', 'gradingScheme.gradingScale.bands',
            'subjects.subject', 'publications.section',
        ]);

        return view('tenant.examinations.exams.show', array_merge($this->examFormOptions(), [
            'exam' => $exam,
            'isPublished' => $exam->publications()->whereNotNull('published_at')->exists(),
        ]));
    }

    public function edit(Exam $exam): View
    {
        $this->assertTenantOwned($exam);

        return view('tenant.examinations.exams.edit', array_merge($this->examFormOptions(), [
            'exam' => $exam,
        ]));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertTenantOwned($exam);
        $exam->update($this->examPayload($request));

        return redirect()
            ->route('tenant.exams.show', $exam)
            ->with('status', __('ui.exam_saved'));
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->assertTenantOwned($exam);
        $exam->delete();

        return redirect()
            ->route('tenant.exams.index')
            ->with('status', __('ui.exam_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function examPayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'academic_term_id' => ['nullable', 'integer', Rule::exists('academic_terms', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'grading_scheme_id' => ['nullable', 'integer', Rule::exists('grading_schemes', 'id')],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::in(Exam::STATUSES)],
            'board_name' => ['nullable', 'string', 'max:255'],
            'board_roll_prefix' => ['nullable', 'string', 'max:64'],
            'board_center_code' => ['nullable', 'string', 'max:64'],
            'board_session' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string'],
        ]);

        $meta = array_filter([
            'center_code' => $data['board_center_code'] ?? null,
            'session' => $data['board_session'] ?? null,
        ]);

        unset($data['board_center_code'], $data['board_session']);
        $data['code'] = $data['code'] ?: null;
        $data['board_meta'] = $meta === [] ? null : $meta;

        return $data;
    }
}

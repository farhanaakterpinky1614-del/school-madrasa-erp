<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Examinations\Concerns\LoadsExamFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\Mark;
use Modules\Examinations\Models\ResultPublication;

class ResultPublishController extends Controller
{
    use LoadsExamFormOptions;

    public function index(Request $request): View
    {
        $options = $this->examFormOptions($request->user());
        $exams = Exam::query()
            ->with(['schoolClass', 'publications'])
            ->withCount('marks')
            ->orderByDesc('starts_on')
            ->get();

        $examId = $request->integer('exam_id') ?: $exams->first()?->id;
        $exam = $examId ? Exam::query()->with(['publications.section', 'subjects'])->find($examId) : null;
        if ($exam) {
            $this->assertTenantOwned($exam);
        }

        $markStats = null;
        if ($exam) {
            $markStats = [
                'total' => Mark::query()->where('exam_id', $exam->id)->count(),
                'draft' => Mark::query()->where('exam_id', $exam->id)->where('entry_status', Mark::ENTRY_DRAFT)->count(),
                'submitted' => Mark::query()->where('exam_id', $exam->id)->where('entry_status', Mark::ENTRY_SUBMITTED)->count(),
            ];
        }

        $sections = ($exam
            ? $options['sections']->where('school_class_id', $exam->school_class_id)
            : collect())->values();

        return view('tenant.examinations.publish.index', array_merge($options, [
            'exams' => $exams,
            'exam' => $exam,
            'examId' => $examId,
            'markStats' => $markStats,
            'sections' => $sections,
            'isPublished' => $exam
                ? $exam->publications()->whereNotNull('published_at')->whereNull('section_id')->exists()
                : false,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', Rule::exists('exams', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'notes' => ['nullable', 'string', 'max:500'],
            'action' => ['required', Rule::in(['publish', 'unpublish'])],
        ]);

        $exam = Exam::query()->findOrFail($data['exam_id']);
        $this->assertTenantOwned($exam);

        $sectionId = isset($data['section_id']) && $data['section_id'] !== ''
            ? (int) $data['section_id']
            : null;

        if ($data['action'] === 'publish') {
            // Promote draft marks to submitted on publish
            Mark::query()
                ->where('exam_id', $exam->id)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->where('entry_status', Mark::ENTRY_DRAFT)
                ->update(['entry_status' => Mark::ENTRY_SUBMITTED]);

            ResultPublication::query()->updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'section_id' => $sectionId,
                ],
                [
                    'published_at' => now('Asia/Dhaka'),
                    'published_by' => $request->user()?->id,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            if ($exam->status === Exam::STATUS_DRAFT || $exam->status === Exam::STATUS_SCHEDULED) {
                $exam->update(['status' => Exam::STATUS_ONGOING]);
            }

            $message = __('ui.results_published');
        } else {
            ResultPublication::query()->updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'section_id' => $sectionId,
                ],
                [
                    'published_at' => null,
                    'published_by' => $request->user()?->id,
                    'notes' => $data['notes'] ?? __('ui.results_unpublished_note'),
                ]
            );
            $message = __('ui.results_unpublished');
        }

        return redirect()
            ->route('tenant.results.publish.index', ['exam_id' => $exam->id])
            ->with('status', $message);
    }
}

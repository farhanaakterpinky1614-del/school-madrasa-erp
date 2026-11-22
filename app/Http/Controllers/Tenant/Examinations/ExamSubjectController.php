<?php

namespace App\Http\Controllers\Tenant\Examinations;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;

class ExamSubjectController extends Controller
{
    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $this->assertTenantOwned($exam);

        $data = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('exam_subjects', 'subject_id')
                    ->where(fn ($q) => $q->where('exam_id', $exam->id)),
            ],
            'full_marks' => ['required', 'numeric', 'min:1', 'max:9999'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:full_marks'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_optional' => ['sometimes', 'boolean'],
        ]);

        ExamSubject::query()->create([
            'exam_id' => $exam->id,
            'subject_id' => $data['subject_id'],
            'full_marks' => $data['full_marks'],
            'pass_marks' => $data['pass_marks'],
            'weight' => $data['weight'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_optional' => $request->boolean('is_optional'),
        ]);

        return back()->with('status', __('ui.exam_subject_saved'));
    }

    public function update(Request $request, Exam $exam, ExamSubject $examSubject): RedirectResponse
    {
        $this->assertTenantOwned($exam);
        $this->assertTenantOwned($examSubject);
        abort_unless((int) $examSubject->exam_id === (int) $exam->id, 404);

        $data = $request->validate([
            'full_marks' => ['required', 'numeric', 'min:1', 'max:9999'],
            'pass_marks' => ['required', 'numeric', 'min:0', 'lte:full_marks'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_optional' => ['sometimes', 'boolean'],
        ]);

        $examSubject->update([
            'full_marks' => $data['full_marks'],
            'pass_marks' => $data['pass_marks'],
            'weight' => $data['weight'] ?? null,
            'sort_order' => $data['sort_order'] ?? $examSubject->sort_order,
            'is_optional' => $request->boolean('is_optional'),
        ]);

        return back()->with('status', __('ui.exam_subject_saved'));
    }

    public function destroy(Exam $exam, ExamSubject $examSubject): RedirectResponse
    {
        $this->assertTenantOwned($exam);
        $this->assertTenantOwned($examSubject);
        abort_unless((int) $examSubject->exam_id === (int) $exam->id, 404);
        $examSubject->delete();

        return back()->with('status', __('ui.exam_subject_deleted'));
    }
}

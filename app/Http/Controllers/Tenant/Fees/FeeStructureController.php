<?php

namespace App\Http\Controllers\Tenant\Fees;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Fees\Concerns\LoadsFeeFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Fees\Models\FeeStructure;
use Modules\Fees\Services\InvoiceLedger;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;

class FeeStructureController extends Controller
{
    use LoadsFeeFormOptions;

    public function index(Request $request): View
    {
        $query = FeeStructure::query()
            ->with(['category', 'academicYear', 'schoolClass'])
            ->orderByDesc('id');

        if ($request->filled('category_id')) {
            $query->where('fee_category_id', $request->integer('category_id'));
        }
        if ($request->filled('year_id')) {
            $query->where('academic_year_id', $request->integer('year_id'));
        }

        return view('tenant.fees.structures.index', array_merge($this->feeFormOptions(), [
            'structuresList' => $query->paginate(20)->withQueryString(),
            'filterCategoryId' => $request->integer('category_id') ?: null,
            'filterYearId' => $request->integer('year_id') ?: null,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        FeeStructure::query()->create($this->payload($request));

        return back()->with('status', __('ui.fee_structure_saved'));
    }

    public function update(Request $request, FeeStructure $structure): RedirectResponse
    {
        $this->assertTenantOwned($structure);
        $structure->update($this->payload($request));

        return back()->with('status', __('ui.fee_structure_saved'));
    }

    public function destroy(FeeStructure $structure): RedirectResponse
    {
        $this->assertTenantOwned($structure);
        $structure->delete();

        return back()->with('status', __('ui.fee_structure_deleted'));
    }

    public function assign(Request $request, FeeStructure $structure, InvoiceLedger $ledger): RedirectResponse
    {
        $this->assertTenantOwned($structure);

        $data = $request->validate([
            'student_id' => ['nullable', 'integer', Rule::exists('students', 'id')],
            'assign_class' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'issue_now' => ['sometimes', 'boolean'],
        ]);

        $issue = $request->boolean('issue_now', true);
        $due = $data['due_date'] ?? now('Asia/Dhaka')->addDays(14)->toDateString();
        $created = 0;

        if (! empty($data['student_id'])) {
            $student = Student::query()->findOrFail($data['student_id']);
            $ledger->createFromStructure($student, $structure, $due, $issue, $request->user()?->id);
            $created = 1;
        } elseif ($request->boolean('assign_class') && $structure->school_class_id) {
            $studentIds = StudentEnrollment::query()
                ->where('school_class_id', $structure->school_class_id)
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->pluck('student_id')
                ->unique();

            foreach ($studentIds as $studentId) {
                $student = Student::query()->find($studentId);
                if (! $student) {
                    continue;
                }
                $ledger->createFromStructure($student, $structure, $due, $issue, $request->user()?->id);
                $created++;
            }
        } else {
            return back()->withErrors(['student_id' => __('ui.select_student_or_class')]);
        }

        return back()->with('status', __('ui.fee_invoices_generated', ['count' => $created]));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'fee_category_id' => ['required', 'integer', Rule::exists('fee_categories', 'id')],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', Rule::in(FeeStructure::FREQUENCIES)],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

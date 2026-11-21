<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentPromotion;
use Modules\Students\Services\PromotionService;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = StudentPromotion::query()
            ->with(['student', 'fromYear', 'toYear', 'fromClass', 'toClass'])
            ->latest()
            ->paginate(20);

        return view('tenant.students.promotions.index', compact('promotions'));
    }

    public function create(Student $student): View
    {
        $this->assertTenantOwned($student);

        return view('tenant.students.promotions.create', [
            'student' => $student,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Student $student, PromotionService $promotions): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'to_year_id' => ['required', 'integer'],
            'to_class_id' => ['required', 'integer'],
            'to_section_id' => ['nullable', 'integer'],
            'roll_number' => ['nullable', 'string', 'max:32'],
            'promoted_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['created_by'] = $request->user()?->id;
        $promotions->promote($student, $data);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.promotion_saved'));
    }
}

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
use Modules\Students\Models\StudentTransfer;
use Modules\Students\Services\TransferService;

class TransferController extends Controller
{
    public function index(): View
    {
        $transfers = StudentTransfer::query()
            ->with(['student', 'fromClass', 'toClass', 'fromSection', 'toSection'])
            ->latest()
            ->paginate(20);

        return view('tenant.students.transfers.index', compact('transfers'));
    }

    public function create(Student $student): View
    {
        $this->assertTenantOwned($student);
        $student->load('currentEnrollment');

        return view('tenant.students.transfers.create', [
            'student' => $student,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Student $student, TransferService $transfers): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer'],
            'to_class_id' => ['required', 'integer'],
            'to_section_id' => ['nullable', 'integer'],
            'roll_number' => ['nullable', 'string', 'max:32'],
            'transferred_on' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['created_by'] = $request->user()?->id;
        $transfers->transfer($student, $data);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.transfer_saved'));
    }
}

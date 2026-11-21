<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Students\Models\StudentImportBatch;
use Modules\Students\Services\BulkStudentImporter;

class StudentImportController extends Controller
{
    public function create(): View
    {
        return view('tenant.students.import.create', [
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function preview(Request $request, BulkStudentImporter $importer): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx,xls'],
        ]);

        $batch = $importer->preview($request->file('file'), (int) $request->user()->id);

        return redirect()->route('tenant.students.import.show', $batch)->with('status', __('ui.import_preview_ready'));
    }

    public function show(StudentImportBatch $batch): View
    {
        $this->assertTenantOwned($batch);

        return view('tenant.students.import.show', [
            'batch' => $batch,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->orderBy('name')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function commit(Request $request, StudentImportBatch $batch, BulkStudentImporter $importer): RedirectResponse
    {
        $this->assertTenantOwned($batch);
        $data = $request->validate([
            'academic_year_id' => ['nullable', 'integer'],
            'school_class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
        ]);

        $importer->commit(
            $batch,
            $data['academic_year_id'] ?? null,
            $data['school_class_id'] ?? null,
            $data['section_id'] ?? null,
        );

        return redirect()->route('tenant.students.index')->with('status', __('ui.import_committed'));
    }
}

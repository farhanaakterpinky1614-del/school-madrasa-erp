<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentDocument;

class StudentDocumentController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $data = $request->validate([
            'doc_type' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $path = $request->file('file')->store('tenants/'.$student->tenant_id.'/students/'.$student->id, 'local');

        StudentDocument::query()->create([
            'student_id' => $student->id,
            'doc_type' => $data['doc_type'],
            'title' => $data['title'],
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => $request->user()?->id,
        ]);

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.document_saved'));
    }

    public function destroy(Student $student, StudentDocument $document): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $this->assertTenantOwned($document);
        abort_unless((int) $document->student_id === (int) $student->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()->route('tenant.students.show', $student)->with('status', __('ui.document_deleted'));
    }
}

<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use App\Core\Tenancy\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\StaffDocument;

class StaffDocumentController extends Controller
{
    public function store(Request $request, Staff $staff): RedirectResponse
    {
        $this->assertTenantOwned($staff);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'max:64'],
            'file' => ['required', 'file', 'max:5120'],
        ]);

        $tenantId = app(CurrentTenant::class)->id();
        $path = $request->file('file')->store(
            "tenants/{$tenantId}/staff/{$staff->id}/documents",
            'local'
        );

        StaffDocument::query()->create([
            'staff_id' => $staff->id,
            'title' => $data['title'],
            'doc_type' => $data['doc_type'] ?? null,
            'file_path' => $path,
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'uploaded_by' => $request->user()?->id,
        ]);

        return redirect()->route('tenant.staff.show', $staff)->with('status', __('ui.document_saved'));
    }

    public function destroy(Staff $staff, StaffDocument $document): RedirectResponse
    {
        $this->assertTenantOwned($staff);
        $this->assertTenantOwned($document);
        abort_unless((int) $document->staff_id === (int) $staff->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()->route('tenant.staff.show', $staff)->with('status', __('ui.document_deleted'));
    }
}

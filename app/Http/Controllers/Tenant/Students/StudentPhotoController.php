<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Students\Models\Student;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPhotoController extends Controller
{
    public function show(Student $student): StreamedResponse
    {
        $this->assertTenantOwned($student);
        abort_unless($student->photo_path && Storage::disk('local')->exists($student->photo_path), 404);

        return Storage::disk('local')->response($student->photo_path);
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenantOwned($student);
        $request->validate([
            'photo' => ['required', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
            'redirect_to' => ['nullable', 'in:profile,id-card'],
        ]);

        $disk = Storage::disk('local');
        if ($student->photo_path && $disk->exists($student->photo_path)) {
            $disk->delete($student->photo_path);
        }

        $path = $request->file('photo')->store(
            'tenants/'.$student->tenant_id.'/students/'.$student->id.'/photo',
            'local'
        );

        $student->update(['photo_path' => $path]);

        $redirect = $request->input('redirect_to') === 'id-card'
            ? route('tenant.students.id-card', $student)
            : route('tenant.students.show', $student);

        return redirect($redirect)->with('status', __('ui.photo_saved'));
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenantOwned($student);

        if ($student->photo_path) {
            Storage::disk('local')->delete($student->photo_path);
            $student->update(['photo_path' => null]);
        }

        $redirect = $request->input('redirect_to') === 'id-card'
            ? route('tenant.students.id-card', $student)
            : route('tenant.students.show', $student);

        return redirect($redirect)->with('status', __('ui.photo_removed'));
    }
}

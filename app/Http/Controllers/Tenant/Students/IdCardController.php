<?php

namespace App\Http\Controllers\Tenant\Students;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Students\Models\Student;
use Modules\Students\Services\QrIdCardService;
use Modules\Tenancy\Models\Institution;

class IdCardController extends Controller
{
    public function show(Student $student, QrIdCardService $qr): View
    {
        $this->assertTenantOwned($student);
        $student->load('currentEnrollment.schoolClass', 'currentEnrollment.section');
        $qrDataUri = $qr->dataUri($student);
        $institution = Institution::query()->first();

        return view('tenant.students.id-cards.show', compact('student', 'qrDataUri', 'institution'));
    }
}

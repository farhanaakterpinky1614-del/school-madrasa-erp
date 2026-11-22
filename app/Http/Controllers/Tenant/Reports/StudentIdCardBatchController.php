<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Reports\Models\IdCardTemplate;
use Modules\Students\Models\Student;
use Modules\Students\Services\QrIdCardService;
use Modules\Tenancy\Models\Institution;

class StudentIdCardBatchController extends Controller
{
    public function index(Request $request): View
    {
        $classes = SchoolClass::query()->orderBy('sort_order')->orderBy('name')->get();
        $sections = Section::query()->orderBy('name')->get();
        $templates = IdCardTemplate::query()
            ->where('card_type', IdCardTemplate::TYPE_STUDENT)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('tenant.reports.id-cards.students', [
            'classes' => $classes,
            'sections' => $sections,
            'templates' => $templates,
            'filterClassId' => $request->query('school_class_id'),
            'filterSectionId' => $request->query('section_id'),
            'filterTemplateId' => $request->query('template_id'),
        ]);
    }

    public function print(Request $request, QrIdCardService $qr): View
    {
        $data = $request->validate([
            'school_class_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'template_id' => ['nullable', 'integer'],
        ]);

        $template = null;
        if (! empty($data['template_id'])) {
            $template = IdCardTemplate::query()
                ->where('card_type', IdCardTemplate::TYPE_STUDENT)
                ->findOrFail($data['template_id']);
            $this->assertTenantOwned($template);
        } else {
            $template = IdCardTemplate::query()
                ->where('card_type', IdCardTemplate::TYPE_STUDENT)
                ->where('is_default', true)
                ->first();
        }

        $students = Student::query()
            ->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
            ->whereHas('currentEnrollment', function ($q) use ($data) {
                if (! empty($data['school_class_id'])) {
                    $q->where('school_class_id', (int) $data['school_class_id']);
                }
                if (! empty($data['section_id'])) {
                    $q->where('section_id', (int) $data['section_id']);
                }
                $q->where('is_current', true);
            })
            ->orderBy('student_code')
            ->limit(200)
            ->get();

        $cards = $students->map(function (Student $student) use ($qr) {
            return [
                'student' => $student,
                'qrDataUri' => $qr->dataUri($student),
                'verifyUrl' => url('/verify/'.(app(\App\Core\Tenancy\CurrentTenant::class)->get()?->slug ?? 'tenant').'/student/'.$student->student_code),
            ];
        });

        $institution = Institution::query()->first();

        return view('tenant.reports.id-cards.students-print', [
            'cards' => $cards,
            'institution' => $institution,
            'template' => $template,
        ]);
    }
}

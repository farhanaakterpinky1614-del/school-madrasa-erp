<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Reports\Models\CertificateIssue;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Reports\Services\CertificateIssueService;
use Modules\Reports\Services\CertificatePdfService;
use Modules\Students\Models\Student;

class CertificateIssueController extends Controller
{
    public function __construct(
        protected CertificateIssueService $issuer,
        protected CertificatePdfService $pdfService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $issues = CertificateIssue::query()
            ->with(['template', 'student', 'issuer'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('issue_number', 'like', '%'.$q.'%')
                        ->orWhereHas('student', function ($s) use ($q) {
                            $s->where('first_name', 'like', '%'.$q.'%')
                                ->orWhere('last_name', 'like', '%'.$q.'%')
                                ->orWhere('student_code', 'like', '%'.$q.'%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $templates = CertificateTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $students = Student::query()
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('student_code')
            ->limit(300)
            ->get();

        return view('tenant.reports.certificates.issues', [
            'issues' => $issues,
            'templates' => $templates,
            'students' => $students,
            'filterQ' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'certificate_template_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $template = CertificateTemplate::query()->findOrFail($data['certificate_template_id']);
        $this->assertTenantOwned($template);

        $student = Student::query()->findOrFail($data['student_id']);
        $this->assertTenantOwned($student);

        $this->issuer->issue(
            $template,
            $student,
            $request->user(),
            notes: $data['notes'] ?? null,
        );

        return back()->with('status', __('ui.certificate_issued'));
    }

    public function pdf(CertificateIssue $issue): Response
    {
        $this->assertTenantOwned($issue);

        return $this->pdfService->download($issue);
    }
}

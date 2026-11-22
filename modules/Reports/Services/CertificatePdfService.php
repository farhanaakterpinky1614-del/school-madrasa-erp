<?php

namespace Modules\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Reports\Models\CertificateIssue;
use Modules\Tenancy\Models\Institution;

class CertificatePdfService
{
    public function __construct(
        protected CertificateIssueService $issues,
        protected QrCredentialService $qr,
    ) {}

    public function download(CertificateIssue $issue): Response
    {
        $issue->load(['template', 'student.currentEnrollment.schoolClass', 'student.currentEnrollment.section', 'issuer']);
        $template = $issue->template;
        $placeholders = $issue->placeholders ?? [];
        $institution = Institution::query()->first();

        $qrDataUri = null;
        if ($template->show_qr && $issue->qr_payload) {
            $qrDataUri = $this->qr->dataUri($issue->qr_payload, 160);
        }

        $pdf = Pdf::loadView('tenant.reports.certificates.pdf', [
            'issue' => $issue,
            'template' => $template,
            'student' => $issue->student,
            'institution' => $institution,
            'title' => $this->issues->renderTitle($template, $placeholders),
            'body' => $this->issues->renderBody($template, $placeholders),
            'qrDataUri' => $qrDataUri,
            'placeholders' => $placeholders,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('certificate-'.$issue->issue_number.'.pdf');
    }
}

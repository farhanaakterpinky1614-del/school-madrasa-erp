<?php

namespace Modules\Reports\Services;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use Illuminate\Support\Str;
use Modules\Reports\Models\CertificateIssue;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Institution;

class CertificateIssueService
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    /**
     * @param  array<string, string>  $extra
     */
    public function issue(
        CertificateTemplate $template,
        Student $student,
        ?User $issuer = null,
        array $extra = [],
        ?string $notes = null,
    ): CertificateIssue {
        $placeholders = $this->defaultPlaceholders($template, $student, $extra);
        $issueNumber = $this->nextIssueNumber($template);

        $slug = $this->currentTenant->get()?->slug ?? 'tenant';
        $qrPayload = 'sme://'.$slug.'/certificate/'.$issueNumber;

        return CertificateIssue::query()->create([
            'certificate_template_id' => $template->id,
            'student_id' => $student->id,
            'issue_number' => $issueNumber,
            'status' => CertificateIssue::STATUS_ISSUED,
            'issued_at' => now('Asia/Dhaka'),
            'issued_by' => $issuer?->id,
            'placeholders' => $placeholders,
            'qr_payload' => $qrPayload,
            'notes' => $notes,
        ]);
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    public function defaultPlaceholders(CertificateTemplate $template, Student $student, array $extra = []): array
    {
        $institution = Institution::query()->first();
        $student->loadMissing('currentEnrollment.schoolClass', 'currentEnrollment.section');

        $base = [
            'student_name' => $student->full_name ?? trim($student->first_name.' '.($student->last_name ?? '')),
            'student_code' => (string) $student->student_code,
            'class_name' => (string) ($student->currentEnrollment?->schoolClass?->name ?? ''),
            'section_name' => (string) ($student->currentEnrollment?->section?->name ?? ''),
            'institution_name' => (string) ($institution?->name ?? $this->currentTenant->get()?->name ?? ''),
            'issue_date' => now('Asia/Dhaka')->format('d M Y'),
            'certificate_type' => $template->certificate_type,
        ];

        return array_merge($base, $extra);
    }

    public function renderBody(CertificateTemplate $template, array $placeholders): string
    {
        $body = $template->body;
        foreach ($placeholders as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return $body;
    }

    public function renderTitle(CertificateTemplate $template, array $placeholders): string
    {
        $title = $template->title;
        foreach ($placeholders as $key => $value) {
            $title = str_replace('{{'.$key.'}}', (string) $value, $title);
        }

        return $title;
    }

    private function nextIssueNumber(CertificateTemplate $template): string
    {
        $year = now('Asia/Dhaka')->format('Y');
        $prefix = 'CERT-'.Str::upper(Str::substr($template->certificate_type, 0, 3)).'-'.$year.'-';

        $latest = CertificateIssue::query()
            ->where('issue_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('issue_number');

        $seq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}

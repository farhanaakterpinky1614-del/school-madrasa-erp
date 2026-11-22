<?php

namespace Modules\Reports\Services;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Reports\Models\IdCardTemplate;
use Modules\Reports\Models\ReportExport;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo ID card templates, certificate templates, one issued certificate, and a sample export.
 */
class DemoReportsProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected CertificateIssueService $certificates,
        protected ReportExportService $exports,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            IdCardTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'card_type' => IdCardTemplate::TYPE_STUDENT,
                    'name' => 'Default student ISO ID-1',
                ],
                [
                    'show_photo' => true,
                    'show_qr' => true,
                    'primary_color' => '#4F46E5',
                    'secondary_color' => '#0F172A',
                    'footer_text' => 'Valid student credential',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            IdCardTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'card_type' => IdCardTemplate::TYPE_STAFF,
                    'name' => 'Default staff ISO ID-1',
                ],
                [
                    'show_photo' => true,
                    'show_qr' => true,
                    'primary_color' => '#4F46E5',
                    'secondary_color' => '#0F172A',
                    'footer_text' => 'Authorized staff credential',
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $completion = CertificateTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'COMPLETION',
                ],
                [
                    'name' => 'Course completion',
                    'certificate_type' => CertificateTemplate::TYPE_COMPLETION,
                    'title' => 'Certificate of Completion',
                    'body' => "This is to certify that {{student_name}} ({{student_code}}) of {{class_name}} has successfully completed the academic programme at {{institution_name}}.\n\nIssued on {{issue_date}}.",
                    'seal_label' => 'Official seal',
                    'signature_label' => 'Principal / Muhtamim',
                    'show_qr' => true,
                    'is_active' => true,
                    'placeholders_help' => '{{student_name}}, {{student_code}}, {{class_name}}, {{institution_name}}, {{issue_date}}',
                ]
            );

            CertificateTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'TRANSFER',
                ],
                [
                    'name' => 'Transfer certificate',
                    'certificate_type' => CertificateTemplate::TYPE_TRANSFER,
                    'title' => 'Transfer Certificate',
                    'body' => "This is to certify that {{student_name}} ({{student_code}}) was a student of {{class_name}} at {{institution_name}} and is released for transfer as of {{issue_date}}.",
                    'seal_label' => 'Official seal',
                    'signature_label' => 'Head of institution',
                    'show_qr' => true,
                    'is_active' => true,
                    'placeholders_help' => '{{student_name}}, {{student_code}}, {{class_name}}, {{institution_name}}, {{issue_date}}',
                ]
            );

            CertificateTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'CHARACTER',
                ],
                [
                    'name' => 'Character certificate',
                    'certificate_type' => CertificateTemplate::TYPE_CHARACTER,
                    'title' => 'Character Certificate',
                    'body' => "This is to certify that {{student_name}} ({{student_code}}) of {{class_name}} bears a good moral character during their stay at {{institution_name}}.\n\nIssued on {{issue_date}}.",
                    'seal_label' => 'Official seal',
                    'signature_label' => 'Principal / Muhtamim',
                    'show_qr' => true,
                    'is_active' => true,
                    'placeholders_help' => '{{student_name}}, {{student_code}}, {{class_name}}, {{institution_name}}, {{issue_date}}',
                ]
            );

            $admin = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', 'like', 'admin%')
                ->orderBy('id')
                ->first();

            $student = Student::query()->orderBy('id')->first();
            if ($student && $completion->issues()->doesntExist()) {
                $this->certificates->issue($completion, $student, $admin);
            }

            if (ReportExport::query()->where('report_type', ReportExport::TYPE_STUDENTS_ROSTER)->doesntExist()) {
                $this->exports->queueAndRun(
                    ReportExport::TYPE_STUDENTS_ROSTER,
                    ReportExport::FORMAT_CSV,
                    $admin,
                );
            }
        });
    }
}

<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Reports\Models\CertificateIssue;
use Modules\Reports\Models\CertificateTemplate;
use Modules\Reports\Models\ReportExport;

class ReportHubController extends Controller
{
    public function index(): View
    {
        return view('tenant.reports.index', [
            'templateCount' => CertificateTemplate::query()->count(),
            'issueCount' => CertificateIssue::query()->count(),
            'exportCount' => ReportExport::query()->count(),
            'completedExports' => ReportExport::query()->where('status', ReportExport::STATUS_COMPLETED)->count(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\SchoolClass;
use Modules\Fees\Models\FeeInvoice;
use Modules\Reports\Models\ReportExport;
use Modules\Reports\Services\ReportExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(protected ReportExportService $exports) {}

    public function index(Request $request): View
    {
        $exports = ReportExport::query()
            ->with('creator')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.reports.exports.index', [
            'exports' => $exports,
            'reportTypes' => ReportExport::TYPES,
            'formats' => ReportExport::FORMATS,
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'invoiceStatuses' => FeeInvoice::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'report_type' => ['required', Rule::in(ReportExport::TYPES)],
            'format' => ['required', Rule::in(ReportExport::FORMATS)],
            'school_class_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:32'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $filters = array_filter([
            'school_class_id' => $data['school_class_id'] ?? null,
            'status' => $data['status'] ?? null,
            'from' => $data['from'] ?? null,
            'to' => $data['to'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $export = $this->exports->queueAndRun(
            $data['report_type'],
            $data['format'],
            $request->user(),
            $filters,
        );

        $message = $export->status === ReportExport::STATUS_COMPLETED
            ? __('ui.report_export_completed')
            : __('ui.report_export_failed');

        return back()->with('status', $message);
    }

    public function download(ReportExport $export): StreamedResponse
    {
        $this->assertTenantOwned($export);
        abort_unless(
            $export->status === ReportExport::STATUS_COMPLETED && $export->file_path,
            404
        );
        abort_unless(Storage::disk('local')->exists($export->file_path), 404);

        return Storage::disk('local')->download(
            $export->file_path,
            $export->original_filename ?? basename($export->file_path)
        );
    }
}

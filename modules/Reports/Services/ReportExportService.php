<?php

namespace Modules\Reports\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Fees\Models\FeeInvoice;
use Modules\Reports\Models\ReportExport;
use Modules\Students\Models\Student;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Throwable;

class ReportExportService
{
    /**
     * Create a queued export row and run it synchronously (demo foundation).
     *
     * @param  array<string, mixed>  $filters
     */
    public function queueAndRun(string $reportType, string $format, ?User $user = null, array $filters = []): ReportExport
    {
        $export = ReportExport::query()->create([
            'report_type' => $reportType,
            'format' => $format,
            'status' => ReportExport::STATUS_QUEUED,
            'filters' => $filters,
            'created_by' => $user?->id,
        ]);

        return $this->run($export);
    }

    public function run(ReportExport $export): ReportExport
    {
        $export->update([
            'status' => ReportExport::STATUS_PROCESSING,
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            [$headers, $rows] = $this->buildRows($export->report_type, $export->filters ?? []);
            $filename = $export->report_type.'-'.now('Asia/Dhaka')->format('Ymd-His').'.'.$export->format;
            $relative = 'exports/'.$export->tenant_id.'/'.$filename;

            Storage::disk('local')->makeDirectory('exports/'.$export->tenant_id);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(array_merge([$headers], $rows), null, 'A1');

            $absolute = Storage::disk('local')->path($relative);
            if ($export->format === ReportExport::FORMAT_CSV) {
                (new CsvWriter($spreadsheet))->save($absolute);
            } else {
                (new XlsxWriter($spreadsheet))->save($absolute);
            }

            $export->update([
                'status' => ReportExport::STATUS_COMPLETED,
                'file_path' => $relative,
                'original_filename' => $filename,
                'row_count' => count($rows),
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $export->update([
                'status' => ReportExport::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }

        return $export->refresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    private function buildRows(string $type, array $filters): array
    {
        return match ($type) {
            ReportExport::TYPE_STUDENTS_ROSTER => $this->studentsRoster($filters),
            ReportExport::TYPE_FEE_INVOICES => $this->feeInvoices($filters),
            ReportExport::TYPE_ATTENDANCE_SUMMARY => $this->attendanceSummary($filters),
            default => throw new \InvalidArgumentException('Unknown report type: '.$type),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    private function studentsRoster(array $filters): array
    {
        $headers = ['Student code', 'Name', 'Gender', 'Status', 'Class', 'Section', 'Phone'];
        $query = Student::query()
            ->with(['currentEnrollment.schoolClass', 'currentEnrollment.section'])
            ->orderBy('student_code');

        if (! empty($filters['school_class_id'])) {
            $classId = (int) $filters['school_class_id'];
            $query->whereHas('currentEnrollment', fn ($q) => $q->where('school_class_id', $classId));
        }

        $rows = $query->get()->map(function (Student $student) {
            return [
                $student->student_code,
                $student->full_name ?? trim($student->first_name.' '.($student->last_name ?? '')),
                $student->gender,
                $student->status,
                $student->currentEnrollment?->schoolClass?->name,
                $student->currentEnrollment?->section?->name,
                $student->phone,
            ];
        })->all();

        return [$headers, $rows];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    private function feeInvoices(array $filters): array
    {
        $headers = ['Invoice #', 'Student', 'Title', 'Status', 'Issue date', 'Due date', 'Total', 'Paid', 'Balance'];
        $query = FeeInvoice::query()->with('student')->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->limit(5000)->get()->map(function (FeeInvoice $invoice) {
            return [
                $invoice->invoice_number,
                $invoice->student?->full_name ?? $invoice->student?->student_code,
                $invoice->title,
                $invoice->status,
                optional($invoice->issue_date)?->format('Y-m-d'),
                optional($invoice->due_date)?->format('Y-m-d'),
                (float) $invoice->total,
                (float) $invoice->paid_amount,
                (float) $invoice->balance,
            ];
        })->all();

        return [$headers, $rows];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    private function attendanceSummary(array $filters): array
    {
        $headers = ['Student code', 'Student', 'Present', 'Absent', 'Late', 'Excused', 'Leave', 'Total marked'];

        $from = $filters['from'] ?? now('Asia/Dhaka')->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now('Asia/Dhaka')->toDateString();

        $records = StudentAttendance::query()
            ->with('student')
            ->whereDate('attendance_date', '>=', $from)
            ->whereDate('attendance_date', '<=', $to)
            ->get();

        $grouped = [];
        foreach ($records as $row) {
            $sid = $row->student_id;
            if (! isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'code' => $row->student?->student_code,
                    'name' => $row->student?->full_name ?? trim(($row->student?->first_name ?? '').' '.($row->student?->last_name ?? '')),
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                    'leave' => 0,
                    'total' => 0,
                ];
            }
            $status = $row->status;
            if (isset($grouped[$sid][$status])) {
                $grouped[$sid][$status]++;
            }
            $grouped[$sid]['total']++;
        }

        $rows = collect($grouped)->map(fn ($g) => [
            $g['code'], $g['name'], $g['present'], $g['absent'], $g['late'], $g['excused'], $g['leave'], $g['total'],
        ])->values()->all();

        return [$headers, $rows];
    }
}

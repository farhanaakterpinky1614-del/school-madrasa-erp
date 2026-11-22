<?php

namespace Modules\Reports\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    use BelongsToTenant;

    public const TYPE_STUDENTS_ROSTER = 'students_roster';

    public const TYPE_FEE_INVOICES = 'fee_invoices';

    public const TYPE_ATTENDANCE_SUMMARY = 'attendance_summary';

    public const TYPES = [
        self::TYPE_STUDENTS_ROSTER,
        self::TYPE_FEE_INVOICES,
        self::TYPE_ATTENDANCE_SUMMARY,
    ];

    public const FORMAT_XLSX = 'xlsx';

    public const FORMAT_CSV = 'csv';

    public const FORMATS = [
        self::FORMAT_XLSX,
        self::FORMAT_CSV,
    ];

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
    ];

    protected $fillable = [
        'tenant_id', 'report_type', 'format', 'status', 'file_path', 'original_filename',
        'filters', 'row_count', 'error_message', 'created_by', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

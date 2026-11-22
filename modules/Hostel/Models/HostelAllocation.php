<?php

namespace Modules\Hostel\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Students\Models\Student;

class HostelAllocation extends Model
{
    use BelongsToTenant;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CHECKED_OUT = 'checked_out';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_CHECKED_OUT,
    ];

    protected $fillable = [
        'tenant_id', 'student_id', 'hostel_bed_id', 'check_in_on',
        'check_out_on', 'status', 'fees_note', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_on' => 'date',
            'check_out_on' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(HostelBed::class, 'hostel_bed_id');
    }
}

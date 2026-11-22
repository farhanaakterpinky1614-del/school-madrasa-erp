<?php

namespace Modules\Reports\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Students\Models\Student;

class CertificateIssue extends Model
{
    use BelongsToTenant;

    public const STATUS_ISSUED = 'issued';

    public const STATUS_REVOKED = 'revoked';

    public const STATUSES = [
        self::STATUS_ISSUED,
        self::STATUS_REVOKED,
    ];

    protected $fillable = [
        'tenant_id', 'certificate_template_id', 'student_id', 'issue_number',
        'status', 'issued_at', 'issued_by', 'placeholders', 'qr_payload', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

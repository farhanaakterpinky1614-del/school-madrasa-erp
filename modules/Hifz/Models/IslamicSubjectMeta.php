<?php

namespace Modules\Hifz\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\Subject;

class IslamicSubjectMeta extends Model
{
    use BelongsToTenant;

    protected $table = 'islamic_subjects_meta';

    protected $fillable = [
        'tenant_id', 'subject_id', 'kitab_name', 'department_label',
        'qawmi_level', 'meta', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}

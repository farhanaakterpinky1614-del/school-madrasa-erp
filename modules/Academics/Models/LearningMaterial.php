<?php

namespace Modules\Academics\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hr\Models\Staff;

class LearningMaterial extends Model
{
    use BelongsToTenant;

    public const TYPE_NOTE = 'note';

    public const TYPE_FILE = 'file';

    public const TYPE_LINK = 'link';

    protected $fillable = [
        'tenant_id', 'academic_year_id', 'school_class_id', 'subject_id',
        'staff_id', 'uploaded_by', 'title', 'description', 'material_type',
        'file_path', 'mime', 'size', 'external_url', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

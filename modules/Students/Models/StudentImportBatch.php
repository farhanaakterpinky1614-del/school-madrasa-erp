<?php

namespace Modules\Students\Models;

use App\Core\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentImportBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'filename', 'status', 'total_rows', 'valid_rows', 'invalid_rows',
        'imported_rows', 'preview_rows', 'preview_errors', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'preview_rows' => 'array',
            'preview_errors' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

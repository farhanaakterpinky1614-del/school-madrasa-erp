<?php

namespace Modules\Admissions\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplicationDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'admission_application_id', 'doc_type', 'title', 'file_path', 'mime', 'size',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}

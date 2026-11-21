<?php

namespace Modules\Admissions\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionFormField extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'admission_form_template_id', 'field_key', 'label', 'field_type',
        'options', 'is_required', 'is_visible', 'sort_order', 'applies_to',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AdmissionFormTemplate::class, 'admission_form_template_id');
    }
}

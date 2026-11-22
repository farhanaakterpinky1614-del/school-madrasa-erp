<?php

namespace App\Http\Controllers\Tenant\Fees\Concerns;

use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeeStructure;
use Modules\Students\Models\Student;

trait LoadsFeeFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function feeFormOptions(): array
    {
        return [
            'categories' => FeeCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'allCategories' => FeeCategory::query()->orderBy('name')->get(),
            'kinds' => FeeCategory::KINDS,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'frequencies' => FeeStructure::FREQUENCIES,
            'students' => Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('first_name')
                ->limit(200)
                ->get(),
            'structures' => FeeStructure::query()
                ->with(['category', 'schoolClass', 'academicYear'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'invoiceStatuses' => FeeInvoice::STATUSES,
            'discountTypes' => FeeDiscount::TYPES,
            'paymentMethods' => FeePayment::METHODS,
        ];
    }
}

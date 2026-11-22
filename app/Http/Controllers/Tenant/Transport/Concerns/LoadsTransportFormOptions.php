<?php

namespace App\Http\Controllers\Tenant\Transport\Concerns;

use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeStructure;
use Modules\Students\Models\Student;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;

trait LoadsTransportFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function transportFormOptions(): array
    {
        return [
            'vehicleTypes' => Vehicle::TYPES,
            'vehicleStatuses' => Vehicle::STATUSES,
            'vehicles' => Vehicle::query()->orderBy('plate_number')->get(),
            'activeVehicles' => Vehicle::query()
                ->where('status', Vehicle::STATUS_ACTIVE)
                ->orderBy('plate_number')
                ->get(),
            'routes' => TransportRoute::query()->where('is_active', true)->orderBy('name')->get(),
            'students' => Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('first_name')
                ->limit(200)
                ->get(),
            'feeCategories' => FeeCategory::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('kind', FeeCategory::KIND_TRANSPORT)
                        ->orWhere('code', 'TRANSPORT');
                })
                ->orderBy('name')
                ->get(),
            'feeStructures' => FeeStructure::query()
                ->with('category')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Tenant\Hostel\Concerns;

use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeStructure;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelBed;
use Modules\Hostel\Models\HostelRoom;
use Modules\Students\Models\Student;

trait LoadsHostelFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function hostelFormOptions(): array
    {
        return [
            'genderTypes' => Hostel::GENDER_TYPES,
            'bedStatuses' => HostelBed::STATUSES,
            'hostels' => Hostel::query()->where('is_active', true)->orderBy('name')->get(),
            'rooms' => HostelRoom::query()->with('hostel')->where('is_active', true)->orderBy('room_number')->get(),
            'availableBeds' => HostelBed::query()
                ->with('room.hostel')
                ->where('status', HostelBed::STATUS_AVAILABLE)
                ->orderBy('label')
                ->get(),
            'students' => Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('first_name')
                ->limit(200)
                ->get(),
            'feeCategories' => FeeCategory::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('kind', FeeCategory::KIND_HOSTEL)
                        ->orWhere('code', 'HOSTEL');
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

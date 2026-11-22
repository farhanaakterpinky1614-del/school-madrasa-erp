<?php

namespace Modules\Hostel\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Hostel\Models\HostelAllocation;
use Modules\Hostel\Models\HostelBed;
use Modules\Students\Models\Student;
use RuntimeException;

class HostelAllocationService
{
    /**
     * @param  array{student_id:int,hostel_bed_id:int,check_in_on?:string|null,notes?:string|null,fees_note?:string|null}  $data
     */
    public function allocate(array $data): HostelAllocation
    {
        return DB::transaction(function () use ($data) {
            $student = Student::query()->findOrFail($data['student_id']);
            $bed = HostelBed::query()->lockForUpdate()->findOrFail($data['hostel_bed_id']);

            if (! $bed->isAvailable()) {
                throw new RuntimeException('Bed is not available for allocation.');
            }

            $bedOccupied = HostelAllocation::query()
                ->where('hostel_bed_id', $bed->id)
                ->where('status', HostelAllocation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->exists();

            if ($bedOccupied) {
                throw new RuntimeException('Bed is already occupied.');
            }

            $studentActive = HostelAllocation::query()
                ->where('student_id', $student->id)
                ->where('status', HostelAllocation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->exists();

            if ($studentActive) {
                throw new RuntimeException('Student already has an active hostel allocation.');
            }

            $allocation = HostelAllocation::query()->create([
                'student_id' => $student->id,
                'hostel_bed_id' => $bed->id,
                'check_in_on' => Carbon::parse(
                    $data['check_in_on'] ?? now('Asia/Dhaka')->toDateString(),
                    'Asia/Dhaka'
                )->toDateString(),
                'status' => HostelAllocation::STATUS_ACTIVE,
                'fees_note' => $data['fees_note'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $bed->update(['status' => HostelBed::STATUS_OCCUPIED]);

            return $allocation->fresh(['student', 'bed.room.hostel']);
        });
    }

    public function checkOut(HostelAllocation $allocation, ?string $checkOutOn = null): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $checkOutOn) {
            $allocation = HostelAllocation::query()->lockForUpdate()->findOrFail($allocation->id);

            if ($allocation->status !== HostelAllocation::STATUS_ACTIVE) {
                throw new RuntimeException('Allocation is not active.');
            }

            $allocation->update([
                'status' => HostelAllocation::STATUS_CHECKED_OUT,
                'check_out_on' => Carbon::parse(
                    $checkOutOn ?? now('Asia/Dhaka')->toDateString(),
                    'Asia/Dhaka'
                )->toDateString(),
            ]);

            $bed = HostelBed::query()->lockForUpdate()->findOrFail($allocation->hostel_bed_id);
            if ($bed->status === HostelBed::STATUS_OCCUPIED) {
                $bed->update(['status' => HostelBed::STATUS_AVAILABLE]);
            }

            return $allocation->fresh(['student', 'bed.room.hostel']);
        });
    }
}

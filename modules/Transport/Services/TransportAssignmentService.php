<?php

namespace Modules\Transport\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Students\Models\Student;
use Modules\Transport\Models\TransportAssignment;
use Modules\Transport\Models\TransportRoute;
use RuntimeException;

class TransportAssignmentService
{
    /**
     * @param  array{student_id:int,transport_route_id:int,pickup_stop?:string|null,assigned_on?:string|null,notes?:string|null,fees_note?:string|null}  $data
     */
    public function assign(array $data): TransportAssignment
    {
        return DB::transaction(function () use ($data) {
            $student = Student::query()->findOrFail($data['student_id']);
            $route = TransportRoute::query()->findOrFail($data['transport_route_id']);

            if (! $route->is_active) {
                throw new RuntimeException('Transport route is inactive.');
            }

            $existing = TransportAssignment::query()
                ->where('student_id', $student->id)
                ->where('status', TransportAssignment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new RuntimeException('Student already has an active transport assignment.');
            }

            return TransportAssignment::query()->create([
                'student_id' => $student->id,
                'transport_route_id' => $route->id,
                'pickup_stop' => $data['pickup_stop'] ?? null,
                'status' => TransportAssignment::STATUS_ACTIVE,
                'assigned_on' => Carbon::parse(
                    $data['assigned_on'] ?? now('Asia/Dhaka')->toDateString(),
                    'Asia/Dhaka'
                )->toDateString(),
                'fees_note' => $data['fees_note'] ?? null,
                'notes' => $data['notes'] ?? null,
            ])->fresh(['student', 'route.vehicle']);
        });
    }

    public function deactivate(TransportAssignment $assignment, ?string $endedOn = null): TransportAssignment
    {
        if ($assignment->status !== TransportAssignment::STATUS_ACTIVE) {
            throw new RuntimeException('Assignment is not active.');
        }

        $assignment->update([
            'status' => TransportAssignment::STATUS_INACTIVE,
            'ended_on' => Carbon::parse(
                $endedOn ?? now('Asia/Dhaka')->toDateString(),
                'Asia/Dhaka'
            )->toDateString(),
        ]);

        return $assignment->fresh(['student', 'route.vehicle']);
    }
}

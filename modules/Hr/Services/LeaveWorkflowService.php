<?php

namespace Modules\Hr\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\LeaveRequest;
use Modules\Hr\Models\Staff;

class LeaveWorkflowService
{
    /**
     * @param  array{leave_type_id:int, starts_on:string, ends_on:string, reason?:string|null}  $data
     */
    public function submit(Staff $staff, array $data): LeaveRequest
    {
        $starts = Carbon::parse($data['starts_on']);
        $ends = Carbon::parse($data['ends_on']);
        $days = max(1, $starts->diffInDays($ends) + 1);

        return LeaveRequest::query()->create([
            'staff_id' => $staff->id,
            'leave_type_id' => $data['leave_type_id'],
            'starts_on' => $starts->toDateString(),
            'ends_on' => $ends->toDateString(),
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => LeaveRequest::STATUS_PENDING,
        ]);
    }

    public function approve(LeaveRequest $request, User $reviewer, ?string $notes = null): LeaveRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $notes) {
            $request->update([
                'status' => LeaveRequest::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $request->fresh();
        });
    }

    public function reject(LeaveRequest $request, User $reviewer, ?string $notes = null): LeaveRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $notes) {
            $request->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $request->fresh();
        });
    }

    public function cancel(LeaveRequest $request): LeaveRequest
    {
        abort_unless($request->status === LeaveRequest::STATUS_PENDING, 422);

        $request->update(['status' => LeaveRequest::STATUS_CANCELLED]);

        return $request->fresh();
    }
}

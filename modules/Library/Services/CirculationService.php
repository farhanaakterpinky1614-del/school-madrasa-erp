<?php

namespace Modules\Library\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Fees\Support\Money;
use Modules\Hr\Models\Staff;
use Modules\Library\Models\LibraryCopy;
use Modules\Library\Models\LibraryFine;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Models\LibrarySetting;
use Modules\Students\Models\Student;
use RuntimeException;

class CirculationService
{
    public function settings(): LibrarySetting
    {
        $existing = LibrarySetting::query()->first();
        if ($existing) {
            return $existing;
        }

        return LibrarySetting::query()->create([
            'fine_mode' => LibrarySetting::FINE_MODE_DAILY,
            'fine_daily_rate' => 10,
            'fine_fixed_amount' => 50,
            'default_loan_days' => 14,
        ]);
    }

    /**
     * @param  array{library_copy_id:int,borrower_type:string,student_id?:int|null,staff_id?:int|null,issued_on?:string,due_on?:string|null,notes?:string|null}  $data
     */
    public function issue(array $data, ?int $issuedBy = null): LibraryIssue
    {
        return DB::transaction(function () use ($data, $issuedBy) {
            $copy = LibraryCopy::query()->lockForUpdate()->findOrFail($data['library_copy_id']);
            if (! $copy->isAvailable()) {
                throw new RuntimeException('Copy is not available for issue.');
            }

            $borrowerType = $data['borrower_type'] ?? LibraryIssue::BORROWER_STUDENT;
            $studentId = null;
            $staffId = null;

            if ($borrowerType === LibraryIssue::BORROWER_STAFF) {
                $staff = Staff::query()->findOrFail($data['staff_id']);
                $staffId = $staff->id;
            } else {
                $student = Student::query()->findOrFail($data['student_id']);
                $studentId = $student->id;
                $borrowerType = LibraryIssue::BORROWER_STUDENT;
            }

            $settings = $this->settings();
            $issuedOn = Carbon::parse($data['issued_on'] ?? now('Asia/Dhaka')->toDateString(), 'Asia/Dhaka')->startOfDay();
            $dueOn = isset($data['due_on']) && $data['due_on']
                ? Carbon::parse($data['due_on'], 'Asia/Dhaka')->startOfDay()
                : $issuedOn->copy()->addDays((int) $settings->default_loan_days);

            $issue = LibraryIssue::query()->create([
                'library_copy_id' => $copy->id,
                'student_id' => $studentId,
                'staff_id' => $staffId,
                'borrower_type' => $borrowerType,
                'issued_on' => $issuedOn->toDateString(),
                'due_on' => $dueOn->toDateString(),
                'status' => LibraryIssue::STATUS_ISSUED,
                'is_overdue' => $dueOn->lt(now('Asia/Dhaka')->startOfDay()),
                'notes' => $data['notes'] ?? null,
                'issued_by' => $issuedBy,
            ]);

            $copy->update(['status' => LibraryCopy::STATUS_ISSUED]);

            return $issue->fresh(['copy.book', 'student', 'staff']);
        });
    }

    public function returnCopy(LibraryIssue $issue, ?int $returnedBy = null, ?string $returnedOn = null): LibraryIssue
    {
        return DB::transaction(function () use ($issue, $returnedBy, $returnedOn) {
            $issue = LibraryIssue::query()->lockForUpdate()->findOrFail($issue->id);
            if (! $issue->isOpen()) {
                throw new RuntimeException('Issue is not open.');
            }

            $returned = Carbon::parse($returnedOn ?? now('Asia/Dhaka')->toDateString(), 'Asia/Dhaka')->startOfDay();
            $due = $issue->due_on->copy()->startOfDay();
            $isOverdue = $returned->gt($due);

            $issue->update([
                'returned_on' => $returned->toDateString(),
                'status' => LibraryIssue::STATUS_RETURNED,
                'is_overdue' => $isOverdue,
                'returned_by' => $returnedBy,
            ]);

            $copy = LibraryCopy::query()->lockForUpdate()->findOrFail($issue->library_copy_id);
            if ($copy->status === LibraryCopy::STATUS_ISSUED) {
                $copy->update(['status' => LibraryCopy::STATUS_AVAILABLE]);
            }

            if ($isOverdue) {
                $this->ensureFine($issue->fresh(), $returned);
            }

            return $issue->fresh(['copy.book', 'student', 'staff', 'fines']);
        });
    }

    public function refreshOverdueFlags(?Carbon $asOf = null): int
    {
        $asOf = ($asOf ?? now('Asia/Dhaka'))->copy()->startOfDay();
        $updated = 0;

        LibraryIssue::query()
            ->where('status', LibraryIssue::STATUS_ISSUED)
            ->whereDate('due_on', '<', $asOf->toDateString())
            ->where('is_overdue', false)
            ->orderBy('id')
            ->chunkById(100, function ($issues) use (&$updated) {
                foreach ($issues as $issue) {
                    $issue->update(['is_overdue' => true]);
                    $updated++;
                }
            });

        return $updated;
    }

    public function ensureFine(LibraryIssue $issue, ?Carbon $asOf = null): ?LibraryFine
    {
        $asOf = ($asOf ?? now('Asia/Dhaka'))->copy()->startOfDay();
        $due = $issue->due_on->copy()->startOfDay();
        if ($asOf->lte($due)) {
            return null;
        }

        $existing = LibraryFine::query()
            ->where('library_issue_id', $issue->id)
            ->where('status', LibraryFine::STATUS_PENDING)
            ->first();

        $settings = $this->settings();
        $days = (int) $due->diffInDays($asOf);
        $mode = $settings->fine_mode;
        $rate = $mode === LibrarySetting::FINE_MODE_FIXED
            ? (float) $settings->fine_fixed_amount
            : (float) $settings->fine_daily_rate;
        $amount = $mode === LibrarySetting::FINE_MODE_FIXED
            ? Money::round($rate)
            : Money::round($rate * $days);

        if ($existing) {
            $existing->update([
                'amount' => $amount,
                'fine_mode' => $mode,
                'rate_applied' => $rate,
                'days_overdue' => $days,
                'calculated_on' => $asOf->toDateString(),
            ]);

            return $existing->fresh();
        }

        return LibraryFine::query()->create([
            'library_issue_id' => $issue->id,
            'amount' => $amount,
            'currency' => 'BDT',
            'status' => LibraryFine::STATUS_PENDING,
            'fine_mode' => $mode,
            'rate_applied' => $rate,
            'days_overdue' => $days,
            'calculated_on' => $asOf->toDateString(),
            'fees_note' => 'Manual library fine — link to Fees invoice optional',
        ]);
    }

    public function markFinePaid(LibraryFine $fine, ?int $settledBy = null, ?string $feesNote = null): LibraryFine
    {
        abort_unless($fine->isPending(), 422);
        $fine->update([
            'status' => LibraryFine::STATUS_PAID,
            'settled_on' => now('Asia/Dhaka')->toDateString(),
            'settled_by' => $settledBy,
            'fees_note' => $feesNote ?: $fine->fees_note,
        ]);

        return $fine->fresh();
    }

    public function markFineWaived(LibraryFine $fine, ?int $settledBy = null, ?string $notes = null): LibraryFine
    {
        abort_unless($fine->isPending(), 422);
        $fine->update([
            'status' => LibraryFine::STATUS_WAIVED,
            'settled_on' => now('Asia/Dhaka')->toDateString(),
            'settled_by' => $settledBy,
            'notes' => $notes ?: $fine->notes,
        ]);

        return $fine->fresh();
    }
}

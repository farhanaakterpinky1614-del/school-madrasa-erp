<?php

namespace Modules\Hr\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\PayrollRun;
use Modules\Hr\Models\Payslip;
use Modules\Hr\Models\Staff;

class PayrollService
{
    public function createRun(int $year, int $month, ?string $notes = null): PayrollRun
    {
        return PayrollRun::query()->create([
            'period_year' => $year,
            'period_month' => $month,
            'status' => PayrollRun::STATUS_DRAFT,
            'notes' => $notes,
        ]);
    }

    public function process(PayrollRun $run, User $processor): PayrollRun
    {
        return DB::transaction(function () use ($run, $processor) {
            abort_unless($run->status === PayrollRun::STATUS_DRAFT, 422);

            $staff = Staff::query()
                ->where('status', Staff::STATUS_ACTIVE)
                ->with('currentSalary.salaryStructure')
                ->get();

            foreach ($staff as $member) {
                $structure = $member->currentSalary?->salaryStructure;
                if (! $structure) {
                    continue;
                }

                $basic = $member->currentSalary->basic_override !== null
                    ? (float) $member->currentSalary->basic_override
                    : (float) $structure->basic_salary;

                $allowances = (float) $structure->house_rent
                    + (float) $structure->medical
                    + (float) $structure->conveyance
                    + (float) $structure->other_allowance;

                $deductions = (float) $structure->deductions;
                $net = $basic + $allowances - $deductions;

                Payslip::query()->updateOrCreate(
                    [
                        'payroll_run_id' => $run->id,
                        'staff_id' => $member->id,
                    ],
                    [
                        'basic' => $basic,
                        'allowances' => $allowances,
                        'deductions' => $deductions,
                        'net_pay' => $net,
                        'status' => Payslip::STATUS_GENERATED,
                    ]
                );
            }

            $run->update([
                'status' => PayrollRun::STATUS_PROCESSED,
                'processed_at' => now(),
                'processed_by' => $processor->id,
            ]);

            return $run->fresh('payslips');
        });
    }

    public function markPaid(PayrollRun $run): PayrollRun
    {
        abort_unless($run->status === PayrollRun::STATUS_PROCESSED, 422);

        $run->payslips()->update(['status' => Payslip::STATUS_PAID]);
        $run->update(['status' => PayrollRun::STATUS_PAID]);

        return $run->fresh('payslips');
    }
}

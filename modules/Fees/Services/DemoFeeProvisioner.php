<?php

namespace Modules\Fees\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\SchoolClass;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeDiscount;
use Modules\Fees\Models\FeeInvoice;
use Modules\Fees\Models\FeePayment;
use Modules\Fees\Models\FeeStructure;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Students\Models\Student;
use Modules\Students\Models\StudentEnrollment;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo fee categories, structures, invoices, payments, discounts, and expenses.
 */
class DemoFeeProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected InvoiceLedger $ledger,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $year = AcademicYear::query()->where('is_current', true)->orderBy('id')->first()
                ?? AcademicYear::query()->orderBy('id')->first();

            $classes = SchoolClass::query()->orderBy('id')->limit(3)->get();
            if (! $year || $classes->isEmpty()) {
                return;
            }

            $categories = [];
            foreach ([
                ['code' => 'TUITION', 'name' => 'Tuition', 'kind' => FeeCategory::KIND_TUITION],
                ['code' => 'EXAM', 'name' => 'Exam fee', 'kind' => FeeCategory::KIND_EXAM],
                ['code' => 'HOSTEL', 'name' => 'Hostel', 'kind' => FeeCategory::KIND_HOSTEL],
                ['code' => 'TRANSPORT', 'name' => 'Transport', 'kind' => FeeCategory::KIND_TRANSPORT],
                ['code' => 'MISC', 'name' => 'Miscellaneous', 'kind' => FeeCategory::KIND_MISC],
            ] as $row) {
                $categories[$row['code']] = FeeCategory::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'kind' => $row['kind'],
                        'description' => 'Demo '.$row['name'].' category',
                        'is_active' => true,
                    ]
                );
            }

            $structures = [];
            $amounts = [
                'TUITION' => 3500,
                'EXAM' => 800,
                'HOSTEL' => 4500,
                'TRANSPORT' => 1200,
                'MISC' => 500,
            ];
            $i = 0;
            foreach ($amounts as $code => $amount) {
                $class = $classes[$i % $classes->count()];
                $structures[$code] = FeeStructure::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'fee_category_id' => $categories[$code]->id,
                        'academic_year_id' => $year->id,
                        'school_class_id' => $class->id,
                        'name' => $categories[$code]->name.' — '.$class->name,
                    ],
                    [
                        'amount' => $amount,
                        'frequency' => $code === 'EXAM' || $code === 'MISC'
                            ? FeeStructure::FREQ_ONE_TIME
                            : FeeStructure::FREQ_MONTHLY,
                        'is_active' => true,
                        'notes' => 'Demo fee structure (BDT)',
                    ]
                );
                $i++;
            }

            $students = Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('id')
                ->limit(5)
                ->get();

            if ($students->isEmpty()) {
                return;
            }

            // Paid invoice
            $paid = $this->ledger->createFromStructure(
                $students[0],
                $structures['TUITION'],
                now('Asia/Dhaka')->addDays(7)->toDateString(),
                true,
            );
            $this->ledger->recordPayment($paid, [
                'amount' => $paid->balance,
                'method' => FeePayment::METHOD_BKASH,
                'paid_on' => now('Asia/Dhaka')->toDateString(),
                'reference' => 'DEMO-BKASH-001',
            ]);

            // Partial invoice with discount
            $partial = $this->ledger->createFromStructure(
                $students[1] ?? $students[0],
                $structures['EXAM'],
                now('Asia/Dhaka')->addDays(14)->toDateString(),
                true,
            );
            $this->ledger->applyDiscount($partial, [
                'type' => FeeDiscount::TYPE_PERCENT,
                'label' => 'Merit scholarship 10%',
                'percent' => 10,
                'notes' => 'Demo scholarship',
            ]);
            $partial = $partial->fresh();
            $half = round(((float) $partial->balance) / 2, 2);
            $this->ledger->recordPayment($partial, [
                'amount' => $half,
                'method' => FeePayment::METHOD_CASH,
                'paid_on' => now('Asia/Dhaka')->toDateString(),
            ]);

            // Issued open balance
            $this->ledger->createFromStructure(
                $students[2] ?? $students[0],
                $structures['TRANSPORT'],
                now('Asia/Dhaka')->addDays(10)->toDateString(),
                true,
            );

            // Draft invoice
            $this->ledger->createManual($students[3] ?? $students[0], [
                'fee_structure_id' => $structures['MISC']->id,
                'title' => 'Admission form fee',
                'subtotal' => 300,
                'due_date' => now('Asia/Dhaka')->addDays(21)->toDateString(),
                'notes' => 'Demo draft invoice',
            ]);

            // Optional: invoice for enrolled student matching class
            $enrollment = StudentEnrollment::query()
                ->where('is_current', true)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->orderBy('id')
                ->first();
            if ($enrollment && isset($students[4])) {
                $this->ledger->createFromStructure(
                    $students[4],
                    $structures['HOSTEL'],
                    now('Asia/Dhaka')->addDays(30)->toDateString(),
                    true,
                );
            }

            $expenseCats = [];
            foreach ([
                ['code' => 'UTIL', 'name' => 'Utilities'],
                ['code' => 'SUPPLY', 'name' => 'Supplies'],
                ['code' => 'MAINT', 'name' => 'Maintenance'],
            ] as $row) {
                $expenseCats[$row['code']] = ExpenseCategory::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'is_active' => true,
                        'description' => 'Demo expense category',
                    ]
                );
            }

            Expense::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Electricity bill — demo',
                    'spent_on' => now('Asia/Dhaka')->subDays(3)->toDateString(),
                ],
                [
                    'expense_category_id' => $expenseCats['UTIL']->id,
                    'amount' => 12500,
                    'payment_method' => Expense::METHODS[1],
                    'reference' => 'DESCO-DEMO',
                    'notes' => 'Institutional expense (not student fee)',
                ]
            );

            Expense::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Whiteboard markers',
                    'spent_on' => now('Asia/Dhaka')->subDays(1)->toDateString(),
                ],
                [
                    'expense_category_id' => $expenseCats['SUPPLY']->id,
                    'amount' => 850,
                    'payment_method' => FeePayment::METHOD_CASH,
                    'notes' => 'Classroom supplies',
                ]
            );

            // Ensure at least one open issued invoice exists after provision
            FeeInvoice::query()->where('status', FeeInvoice::STATUS_ISSUED)->exists();
        });
    }
}

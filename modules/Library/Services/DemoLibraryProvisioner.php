<?php

namespace Modules\Library\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\Staff;
use Modules\Library\Models\LibraryBook;
use Modules\Library\Models\LibraryCopy;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Models\LibrarySetting;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo catalog, copies, issues, overdue, and fines.
 */
class DemoLibraryProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected CirculationService $circulation,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            LibrarySetting::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'fine_mode' => LibrarySetting::FINE_MODE_DAILY,
                    'fine_daily_rate' => 10,
                    'fine_fixed_amount' => 50,
                    'default_loan_days' => 14,
                ]
            );

            $catalog = [
                [
                    'title' => 'Bangla Grammar Essentials',
                    'author' => 'Dr. R. Ahmed',
                    'isbn' => '9789841234001',
                    'category' => 'Language',
                    'subject_tags' => 'bangla,grammar',
                    'publisher' => 'Dhaka Academic Press',
                    'language' => LibraryBook::LANG_BN,
                ],
                [
                    'title' => 'Introduction to Islamic Studies',
                    'author' => 'Ustaz Karim',
                    'isbn' => '9789841234002',
                    'category' => 'Islamic',
                    'subject_tags' => 'deen,foundation',
                    'publisher' => 'Madrasa Books BD',
                    'language' => LibraryBook::LANG_EN,
                ],
                [
                    'title' => 'Qur\'an Recitation Guide',
                    'author' => 'Sheikh Yusuf',
                    'isbn' => '9789841234003',
                    'category' => 'Hifz',
                    'subject_tags' => 'tajweed,quran',
                    'publisher' => 'Al-Noor Publications',
                    'language' => LibraryBook::LANG_AR,
                ],
                [
                    'title' => 'Mathematics for Class 8',
                    'author' => 'N. Chowdhury',
                    'isbn' => '9789841234004',
                    'category' => 'Mathematics',
                    'subject_tags' => 'math,class-8',
                    'publisher' => 'NCTB Companion',
                    'language' => LibraryBook::LANG_EN,
                ],
            ];

            $books = [];
            foreach ($catalog as $row) {
                $books[] = LibraryBook::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'isbn' => $row['isbn'],
                    ],
                    [
                        ...$row,
                        'notes' => 'Demo catalog title',
                        'is_active' => true,
                    ]
                );
            }

            $copies = [];
            $accession = 1001;
            foreach ($books as $index => $book) {
                for ($i = 1; $i <= 2; $i++) {
                    $acc = 'ACC-'.str_pad((string) $accession, 5, '0', STR_PAD_LEFT);
                    $copies[] = LibraryCopy::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'accession_no' => $acc,
                        ],
                        [
                            'library_book_id' => $book->id,
                            'barcode' => 'BC'.$accession,
                            'condition' => $i === 1 ? LibraryCopy::CONDITION_NEW : LibraryCopy::CONDITION_GOOD,
                            'status' => LibraryCopy::STATUS_AVAILABLE,
                            'shelf_location' => 'Shelf '.chr(65 + $index).'-'.$i,
                            'notes' => null,
                        ]
                    );
                    $accession++;
                }
            }

            $students = Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('id')
                ->limit(3)
                ->get();

            $staff = Staff::query()
                ->where('status', Staff::STATUS_ACTIVE)
                ->orderBy('id')
                ->first();

            if ($students->isEmpty() || count($copies) < 3) {
                return;
            }

            // Active issue (not overdue)
            $open = LibraryIssue::query()
                ->where('library_copy_id', $copies[0]->id)
                ->where('status', LibraryIssue::STATUS_ISSUED)
                ->first();
            if (! $open) {
                $this->circulation->issue([
                    'library_copy_id' => $copies[0]->id,
                    'borrower_type' => LibraryIssue::BORROWER_STUDENT,
                    'student_id' => $students[0]->id,
                    'issued_on' => now('Asia/Dhaka')->subDays(3)->toDateString(),
                    'due_on' => now('Asia/Dhaka')->addDays(11)->toDateString(),
                    'notes' => 'Demo open loan',
                ]);
            }

            // Overdue issue → fine
            $overdueCopy = $copies[1]->fresh();
            if ($overdueCopy->status === LibraryCopy::STATUS_AVAILABLE) {
                $overdue = $this->circulation->issue([
                    'library_copy_id' => $overdueCopy->id,
                    'borrower_type' => LibraryIssue::BORROWER_STUDENT,
                    'student_id' => $students[1]->id ?? $students[0]->id,
                    'issued_on' => now('Asia/Dhaka')->subDays(20)->toDateString(),
                    'due_on' => now('Asia/Dhaka')->subDays(6)->toDateString(),
                    'notes' => 'Demo overdue loan',
                ]);
                $overdue->update(['is_overdue' => true]);
                $this->circulation->ensureFine($overdue->fresh());
            }

            // Returned history + staff borrower optional
            $histCopy = $copies[2]->fresh();
            if ($histCopy->status === LibraryCopy::STATUS_AVAILABLE) {
                $hist = $this->circulation->issue([
                    'library_copy_id' => $histCopy->id,
                    'borrower_type' => $staff
                        ? LibraryIssue::BORROWER_STAFF
                        : LibraryIssue::BORROWER_STUDENT,
                    'student_id' => $staff ? null : ($students[2]->id ?? $students[0]->id),
                    'staff_id' => $staff?->id,
                    'issued_on' => now('Asia/Dhaka')->subDays(30)->toDateString(),
                    'due_on' => now('Asia/Dhaka')->subDays(16)->toDateString(),
                    'notes' => 'Demo returned loan',
                ]);
                $this->circulation->returnCopy(
                    $hist,
                    null,
                    now('Asia/Dhaka')->subDays(10)->toDateString()
                );
            }
        });
    }
}

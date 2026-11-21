<?php

namespace App\Http\Controllers\Tenant\Library\Concerns;

use Modules\Hr\Models\Staff;
use Modules\Library\Models\LibraryBook;
use Modules\Library\Models\LibraryCopy;
use Modules\Library\Models\LibraryFine;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Models\LibrarySetting;
use Modules\Library\Services\CirculationService;
use Modules\Students\Models\Student;

trait LoadsLibraryFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function libraryFormOptions(): array
    {
        return [
            'languages' => LibraryBook::LANGUAGES,
            'copyStatuses' => LibraryCopy::STATUSES,
            'conditions' => LibraryCopy::CONDITIONS,
            'issueStatuses' => LibraryIssue::STATUSES,
            'borrowerTypes' => LibraryIssue::BORROWER_TYPES,
            'fineStatuses' => LibraryFine::STATUSES,
            'fineModes' => LibrarySetting::FINE_MODES,
            'books' => LibraryBook::query()->where('is_active', true)->orderBy('title')->get(),
            'availableCopies' => LibraryCopy::query()
                ->with('book')
                ->where('status', LibraryCopy::STATUS_AVAILABLE)
                ->orderBy('accession_no')
                ->get(),
            'students' => Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('first_name')
                ->limit(200)
                ->get(),
            'staffMembers' => Staff::query()
                ->where('status', Staff::STATUS_ACTIVE)
                ->orderBy('first_name')
                ->limit(200)
                ->get(),
            'settings' => app(CirculationService::class)->settings(),
        ];
    }
}

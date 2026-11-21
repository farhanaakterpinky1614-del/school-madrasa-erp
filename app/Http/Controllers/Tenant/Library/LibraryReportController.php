<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Library\Models\LibraryCopy;
use Modules\Library\Models\LibraryFine;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Services\CirculationService;

class LibraryReportController extends Controller
{
    use LoadsLibraryFormOptions;

    public function __construct(protected CirculationService $circulation) {}

    public function index(Request $request): View
    {
        $this->circulation->refreshOverdueFlags();

        $openIssues = LibraryIssue::query()
            ->with(['copy.book', 'student', 'staff'])
            ->where('status', LibraryIssue::STATUS_ISSUED)
            ->orderBy('due_on')
            ->limit(50)
            ->get();

        $overdueIssues = LibraryIssue::query()
            ->with(['copy.book', 'student', 'staff', 'fines'])
            ->where('status', LibraryIssue::STATUS_ISSUED)
            ->where('is_overdue', true)
            ->orderBy('due_on')
            ->limit(50)
            ->get();

        $pendingFines = LibraryFine::query()
            ->where('status', LibraryFine::STATUS_PENDING)
            ->sum('amount');

        return view('tenant.library.reports.index', array_merge($this->libraryFormOptions(), [
            'openIssues' => $openIssues,
            'overdueIssues' => $overdueIssues,
            'counts' => [
                'available' => LibraryCopy::query()->where('status', LibraryCopy::STATUS_AVAILABLE)->count(),
                'issued' => LibraryCopy::query()->where('status', LibraryCopy::STATUS_ISSUED)->count(),
                'overdue' => $overdueIssues->count(),
                'pending_fines_bdt' => (float) $pendingFines,
            ],
        ]));
    }
}

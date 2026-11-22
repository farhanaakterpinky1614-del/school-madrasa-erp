<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Services\CirculationService;
use RuntimeException;

class LibraryIssueController extends Controller
{
    use LoadsLibraryFormOptions;

    public function __construct(protected CirculationService $circulation) {}

    public function index(Request $request): View
    {
        $this->circulation->refreshOverdueFlags();

        $status = (string) $request->query('status', '');
        $overdueOnly = $request->boolean('overdue');

        $issues = LibraryIssue::query()
            ->with(['copy.book', 'student', 'staff'])
            ->when($status !== '' && in_array($status, LibraryIssue::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($overdueOnly, fn ($q) => $q->where('is_overdue', true)->where('status', LibraryIssue::STATUS_ISSUED))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.library.issues.index', array_merge($this->libraryFormOptions(), [
            'issues' => $issues,
            'filterStatus' => $status,
            'filterOverdue' => $overdueOnly,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'library_copy_id' => ['required', 'integer'],
            'borrower_type' => ['required', Rule::in(LibraryIssue::BORROWER_TYPES)],
            'student_id' => ['nullable', 'integer', 'required_if:borrower_type,student'],
            'staff_id' => ['nullable', 'integer', 'required_if:borrower_type,staff'],
            'issued_on' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->circulation->issue($data, $request->user()?->id);
        } catch (RuntimeException $e) {
            return back()->withErrors(['library_copy_id' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.library_issue_saved'));
    }

    public function returnCopy(Request $request, LibraryIssue $issue): RedirectResponse
    {
        $this->assertTenantOwned($issue);

        $data = $request->validate([
            'returned_on' => ['nullable', 'date'],
        ]);

        try {
            $this->circulation->returnCopy($issue, $request->user()?->id, $data['returned_on'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['returned_on' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.library_issue_returned'));
    }
}

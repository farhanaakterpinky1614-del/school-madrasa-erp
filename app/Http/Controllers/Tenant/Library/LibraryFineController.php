<?php

namespace App\Http\Controllers\Tenant\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Library\Concerns\LoadsLibraryFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Library\Models\LibraryFine;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Services\CirculationService;

class LibraryFineController extends Controller
{
    use LoadsLibraryFormOptions;

    public function __construct(protected CirculationService $circulation) {}

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $fines = LibraryFine::query()
            ->with(['issue.copy.book', 'issue.student', 'issue.staff'])
            ->when($status !== '' && in_array($status, LibraryFine::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.library.fines.index', array_merge($this->libraryFormOptions(), [
            'fines' => $fines,
            'filterStatus' => $status,
        ]));
    }

    public function recalculate(Request $request, LibraryIssue $issue): RedirectResponse
    {
        $this->assertTenantOwned($issue);
        $this->circulation->ensureFine($issue);
        $issue->update(['is_overdue' => $issue->due_on->lt(now('Asia/Dhaka')->startOfDay())]);

        return back()->with('status', __('ui.library_fine_recalculated'));
    }

    public function markPaid(Request $request, LibraryFine $fine): RedirectResponse
    {
        $this->assertTenantOwned($fine);
        $data = $request->validate([
            'fees_note' => ['nullable', 'string', 'max:255'],
        ]);
        $this->circulation->markFinePaid($fine, $request->user()?->id, $data['fees_note'] ?? null);

        return back()->with('status', __('ui.library_fine_paid'));
    }

    public function markWaived(Request $request, LibraryFine $fine): RedirectResponse
    {
        $this->assertTenantOwned($fine);
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);
        $this->circulation->markFineWaived($fine, $request->user()?->id, $data['notes'] ?? null);

        return back()->with('status', __('ui.library_fine_waived'));
    }
}

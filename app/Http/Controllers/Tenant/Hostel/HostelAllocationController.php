<?php

namespace App\Http\Controllers\Tenant\Hostel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hostel\Concerns\LoadsHostelFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hostel\Models\HostelAllocation;
use Modules\Hostel\Services\HostelAllocationService;
use RuntimeException;

class HostelAllocationController extends Controller
{
    use LoadsHostelFormOptions;

    public function __construct(protected HostelAllocationService $allocations) {}

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $rows = HostelAllocation::query()
            ->with(['student', 'bed.room.hostel'])
            ->when($status !== '' && in_array($status, HostelAllocation::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hostel.allocations.index', array_merge($this->hostelFormOptions(), [
            'allocations' => $rows,
            'filterStatus' => $status,
            'allocationStatuses' => HostelAllocation::STATUSES,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'hostel_bed_id' => ['required', 'integer'],
            'check_in_on' => ['nullable', 'date'],
            'fees_note' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->allocations->allocate($data);
        } catch (RuntimeException $e) {
            return back()->withErrors(['hostel_bed_id' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.hostel_allocation_saved'));
    }

    public function checkOut(Request $request, HostelAllocation $allocation): RedirectResponse
    {
        $this->assertTenantOwned($allocation);

        $data = $request->validate([
            'check_out_on' => ['nullable', 'date'],
        ]);

        try {
            $this->allocations->checkOut($allocation, $data['check_out_on'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['check_out_on' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.hostel_allocation_checked_out'));
    }
}

<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hr\Models\LeaveRequest;
use Modules\Hr\Models\LeaveType;
use Modules\Hr\Models\Staff;
use Modules\Hr\Services\LeaveWorkflowService;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = LeaveRequest::query()
            ->with(['staff', 'leaveType', 'reviewer'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hr.leave-requests.index', compact('requests'));
    }

    public function create(): View
    {
        $staff = Staff::query()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get();
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();

        return view('tenant.hr.leave-requests.create', compact('staff', 'leaveTypes'));
    }

    public function store(Request $request, LeaveWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')],
            'leave_type_id' => ['required', 'integer', Rule::exists('leave_types', 'id')],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $staff = Staff::query()->findOrFail($data['staff_id']);
        $this->assertTenantOwned($staff);
        $leaveType = LeaveType::query()->findOrFail($data['leave_type_id']);
        $this->assertTenantOwned($leaveType);

        $workflow->submit($staff, $data);

        return redirect()->route('tenant.leave-requests.index')->with('status', __('ui.leave_submitted'));
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->assertTenantOwned($leaveRequest);
        $leaveRequest->load(['staff', 'leaveType', 'reviewer']);

        return view('tenant.hr.leave-requests.show', ['leaveRequest' => $leaveRequest]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest, LeaveWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($leaveRequest);
        abort_unless($leaveRequest->status === LeaveRequest::STATUS_PENDING, 422);
        $notes = $request->validate(['review_notes' => ['nullable', 'string', 'max:500']])['review_notes'] ?? null;
        $workflow->approve($leaveRequest, $request->user(), $notes);

        return redirect()->route('tenant.leave-requests.show', $leaveRequest)->with('status', __('ui.leave_approved'));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest, LeaveWorkflowService $workflow): RedirectResponse
    {
        $this->assertTenantOwned($leaveRequest);
        abort_unless($leaveRequest->status === LeaveRequest::STATUS_PENDING, 422);
        $notes = $request->validate(['review_notes' => ['nullable', 'string', 'max:500']])['review_notes'] ?? null;
        $workflow->reject($leaveRequest, $request->user(), $notes);

        return redirect()->route('tenant.leave-requests.show', $leaveRequest)->with('status', __('ui.leave_rejected'));
    }
}

<?php

namespace App\Http\Controllers\Tenant\Transport;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Transport\Concerns\LoadsTransportFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Transport\Models\TransportAssignment;
use Modules\Transport\Services\TransportAssignmentService;
use RuntimeException;

class TransportAssignmentController extends Controller
{
    use LoadsTransportFormOptions;

    public function __construct(protected TransportAssignmentService $assignments) {}

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $rows = TransportAssignment::query()
            ->with(['student', 'route.vehicle'])
            ->when($status !== '' && in_array($status, TransportAssignment::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.transport.assignments.index', array_merge($this->transportFormOptions(), [
            'assignments' => $rows,
            'filterStatus' => $status,
            'assignmentStatuses' => TransportAssignment::STATUSES,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'transport_route_id' => ['required', 'integer'],
            'pickup_stop' => ['nullable', 'string', 'max:255'],
            'assigned_on' => ['nullable', 'date'],
            'fees_note' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->assignments->assign($data);
        } catch (RuntimeException $e) {
            return back()->withErrors(['student_id' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.transport_assignment_saved'));
    }

    public function deactivate(Request $request, TransportAssignment $assignment): RedirectResponse
    {
        $this->assertTenantOwned($assignment);

        $data = $request->validate([
            'ended_on' => ['nullable', 'date'],
        ]);

        try {
            $this->assignments->deactivate($assignment, $data['ended_on'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['ended_on' => $e->getMessage()]);
        }

        return back()->with('status', __('ui.transport_assignment_deactivated'));
    }
}

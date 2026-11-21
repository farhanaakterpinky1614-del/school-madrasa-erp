<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hr\Models\PayrollRun;
use Modules\Hr\Services\PayrollService;

class PayrollController extends Controller
{
    public function index(): View
    {
        $runs = PayrollRun::query()->withCount('payslips')->orderByDesc('period_year')->orderByDesc('period_month')->paginate(20);

        return view('tenant.hr.payroll.index', compact('runs'));
    }

    public function create(): View
    {
        return view('tenant.hr.payroll.create');
    }

    public function store(Request $request, PayrollService $payroll): RedirectResponse
    {
        $data = $request->validate([
            'period_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $run = $payroll->createRun((int) $data['period_year'], (int) $data['period_month'], $data['notes'] ?? null);

        return redirect()->route('tenant.payroll.show', $run)->with('status', __('ui.payroll_created'));
    }

    public function show(PayrollRun $payroll): View
    {
        $this->assertTenantOwned($payroll);
        $payroll->load(['payslips.staff', 'processor']);

        return view('tenant.hr.payroll.show', ['run' => $payroll]);
    }

    public function process(Request $request, PayrollRun $payroll, PayrollService $service): RedirectResponse
    {
        $this->assertTenantOwned($payroll);
        $service->process($payroll, $request->user());

        return redirect()->route('tenant.payroll.show', $payroll)->with('status', __('ui.payroll_processed'));
    }

    public function markPaid(PayrollRun $payroll, PayrollService $service): RedirectResponse
    {
        $this->assertTenantOwned($payroll);
        $service->markPaid($payroll);

        return redirect()->route('tenant.payroll.show', $payroll)->with('status', __('ui.payroll_paid'));
    }
}

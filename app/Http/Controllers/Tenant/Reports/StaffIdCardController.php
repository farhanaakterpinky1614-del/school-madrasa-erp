<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hr\Models\Staff;
use Modules\Reports\Models\IdCardTemplate;
use Modules\Reports\Services\StaffQrIdCardService;
use Modules\Tenancy\Models\Institution;

class StaffIdCardController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $staff = Staff::query()
            ->with(['designation', 'department'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('first_name', 'like', '%'.$q.'%')
                        ->orWhere('last_name', 'like', '%'.$q.'%')
                        ->orWhere('employee_code', 'like', '%'.$q.'%');
                });
            })
            ->where('status', Staff::STATUS_ACTIVE)
            ->orderBy('employee_code')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.reports.id-cards.staff', [
            'staff' => $staff,
            'filterQ' => $q,
        ]);
    }

    public function show(Staff $staff, StaffQrIdCardService $qr): View
    {
        $this->assertTenantOwned($staff);
        $staff->load(['designation', 'department', 'branch']);

        $template = IdCardTemplate::query()
            ->where('card_type', IdCardTemplate::TYPE_STAFF)
            ->where('is_default', true)
            ->first();

        $institution = Institution::query()->first();

        return view('tenant.reports.id-cards.staff-show', [
            'staff' => $staff,
            'qrDataUri' => $qr->dataUri($staff),
            'verifyUrl' => $qr->verifyStubUrl($staff),
            'institution' => $institution,
            'template' => $template,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Tenant\Hostel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hostel\Concerns\LoadsHostelFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Fees\Support\Money;
use Modules\Hostel\Models\Hostel;

class HostelController extends Controller
{
    use LoadsHostelFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $hostels = Hostel::query()
            ->with(['feeCategory'])
            ->withCount('rooms')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('code', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hostel.hostels.index', array_merge($this->hostelFormOptions(), [
            'hostelList' => $hostels,
            'filterQ' => $q,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        Hostel::query()->create($this->payload($request));

        return back()->with('status', __('ui.hostel_saved'));
    }

    public function update(Request $request, Hostel $hostel): RedirectResponse
    {
        $this->assertTenantOwned($hostel);
        $hostel->update($this->payload($request));

        return back()->with('status', __('ui.hostel_saved'));
    }

    public function destroy(Hostel $hostel): RedirectResponse
    {
        $this->assertTenantOwned($hostel);
        abort_if($hostel->rooms()->exists(), 422);
        $hostel->delete();

        return back()->with('status', __('ui.hostel_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'gender_type' => ['required', Rule::in(Hostel::GENDER_TYPES)],
            'capacity_notes' => ['nullable', 'string'],
            'monthly_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'fee_category_id' => ['nullable', 'integer'],
            'fee_structure_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['monthly_fee_amount'] = isset($data['monthly_fee_amount'])
            ? Money::round($data['monthly_fee_amount'])
            : null;
        $data['currency'] = 'BDT';
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

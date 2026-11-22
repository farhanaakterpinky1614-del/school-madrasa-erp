<?php

namespace App\Http\Controllers\Tenant\Transport;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Transport\Concerns\LoadsTransportFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Fees\Support\Money;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;

class TransportRouteController extends Controller
{
    use LoadsTransportFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $routes = TransportRoute::query()
            ->with(['vehicle', 'feeCategory'])
            ->withCount(['assignments as active_assignments_count' => fn ($query) => $query->where('status', 'active')])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('code', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.transport.routes.index', array_merge($this->transportFormOptions(), [
            'transportRoutes' => $routes,
            'filterQ' => $q,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        TransportRoute::query()->create($this->payload($request));

        return back()->with('status', __('ui.transport_route_saved'));
    }

    public function update(Request $request, TransportRoute $transportRoute): RedirectResponse
    {
        $this->assertTenantOwned($transportRoute);
        $transportRoute->update($this->payload($request));

        return back()->with('status', __('ui.transport_route_saved'));
    }

    public function destroy(TransportRoute $transportRoute): RedirectResponse
    {
        $this->assertTenantOwned($transportRoute);
        abort_if($transportRoute->assignments()->exists(), 422);
        $transportRoute->delete();

        return back()->with('status', __('ui.transport_route_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'stops' => ['nullable', 'string'],
            'vehicle_id' => ['nullable', 'integer'],
            'monthly_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'fee_category_id' => ['nullable', 'integer'],
            'fee_structure_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['vehicle_id'])) {
            $this->assertTenantOwned(Vehicle::query()->findOrFail($data['vehicle_id']));
        }

        $data['monthly_fee_amount'] = Money::round($data['monthly_fee_amount'] ?? 0);
        $data['currency'] = 'BDT';
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

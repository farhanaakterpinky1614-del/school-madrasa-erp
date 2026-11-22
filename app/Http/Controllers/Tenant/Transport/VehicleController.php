<?php

namespace App\Http\Controllers\Tenant\Transport;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Transport\Concerns\LoadsTransportFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Transport\Models\Vehicle;

class VehicleController extends Controller
{
    use LoadsTransportFormOptions;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $vehicles = Vehicle::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('plate_number', 'like', '%'.$q.'%')
                        ->orWhere('driver_name', 'like', '%'.$q.'%')
                        ->orWhere('driver_phone', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '' && in_array($status, Vehicle::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->orderBy('plate_number')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.transport.vehicles.index', array_merge($this->transportFormOptions(), [
            'vehicles' => $vehicles,
            'filterQ' => $q,
            'filterStatus' => $status,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        Vehicle::query()->create($this->payload($request));

        return back()->with('status', __('ui.vehicle_saved'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->assertTenantOwned($vehicle);
        $vehicle->update($this->payload($request));

        return back()->with('status', __('ui.vehicle_saved'));
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->assertTenantOwned($vehicle);
        abort_if($vehicle->routes()->exists(), 422);
        $vehicle->delete();

        return back()->with('status', __('ui.vehicle_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:32'],
            'type' => ['required', Rule::in(Vehicle::TYPES)],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::in(Vehicle::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        return $data;
    }
}

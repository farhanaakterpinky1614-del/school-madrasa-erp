<?php

namespace App\Http\Controllers\Tenant\Hostel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hostel\Concerns\LoadsHostelFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelBed;
use Modules\Hostel\Models\HostelRoom;

class HostelRoomController extends Controller
{
    use LoadsHostelFormOptions;

    public function index(Request $request): View
    {
        $hostelId = (int) $request->query('hostel_id', 0);

        $rooms = HostelRoom::query()
            ->with(['hostel', 'beds'])
            ->withCount([
                'beds',
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', HostelBed::STATUS_OCCUPIED),
            ])
            ->when($hostelId > 0, fn ($q) => $q->where('hostel_id', $hostelId))
            ->orderBy('room_number')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hostel.rooms.index', array_merge($this->hostelFormOptions(), [
            'hostelRooms' => $rooms,
            'filterHostelId' => $hostelId,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->payload($request);
        $labels = $this->parseBedLabels($request->input('bed_labels'), (int) $data['bed_count']);

        DB::transaction(function () use ($data, $labels) {
            $room = HostelRoom::query()->create($data);
            foreach ($labels as $label) {
                HostelBed::query()->create([
                    'hostel_room_id' => $room->id,
                    'label' => $label,
                    'status' => HostelBed::STATUS_AVAILABLE,
                ]);
            }
        });

        return back()->with('status', __('ui.hostel_room_saved'));
    }

    public function update(Request $request, HostelRoom $hostelRoom): RedirectResponse
    {
        $this->assertTenantOwned($hostelRoom);
        $hostelRoom->update($this->payload($request, $hostelRoom));

        return back()->with('status', __('ui.hostel_room_saved'));
    }

    public function destroy(HostelRoom $hostelRoom): RedirectResponse
    {
        $this->assertTenantOwned($hostelRoom);
        abort_if($hostelRoom->beds()->where('status', HostelBed::STATUS_OCCUPIED)->exists(), 422);
        $hostelRoom->delete();

        return back()->with('status', __('ui.hostel_room_deleted'));
    }

    public function updateBed(Request $request, HostelBed $bed): RedirectResponse
    {
        $this->assertTenantOwned($bed);

        $data = $request->validate([
            'status' => ['required', Rule::in(HostelBed::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === HostelBed::STATUS_AVAILABLE
            && $bed->allocations()->where('status', 'active')->exists()) {
            return back()->withErrors(['status' => __('ui.hostel_bed_still_allocated')]);
        }

        if ($data['status'] === HostelBed::STATUS_OCCUPIED
            && ! $bed->allocations()->where('status', 'active')->exists()) {
            return back()->withErrors(['status' => __('ui.hostel_bed_mark_occupied_via_allocation')]);
        }

        $bed->update($data);

        return back()->with('status', __('ui.hostel_bed_saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?HostelRoom $existing = null): array
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'integer'],
            'room_number' => ['required', 'string', 'max:32'],
            'floor' => ['nullable', 'string', 'max:32'],
            'bed_count' => [$existing ? 'sometimes' : 'required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->assertTenantOwned(Hostel::query()->findOrFail($data['hostel_id']));
        $data['is_active'] = $request->boolean('is_active', true);

        if ($existing) {
            unset($data['bed_count']);
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function parseBedLabels(?string $raw, int $bedCount): array
    {
        $labels = [];
        if ($raw !== null && trim($raw) !== '') {
            $labels = array_values(array_filter(array_map(
                'trim',
                preg_split('/[\n,]+/', $raw) ?: []
            ), static fn ($item) => $item !== ''));
        }

        if ($labels === []) {
            for ($i = 0; $i < $bedCount; $i++) {
                $labels[] = chr(65 + $i);
            }
        }

        return array_slice($labels, 0, $bedCount);
    }
}

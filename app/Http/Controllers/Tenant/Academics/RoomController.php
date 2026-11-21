<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Academics\Concerns\LoadsAcademicFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Room;

class RoomController extends Controller
{
    use LoadsAcademicFormOptions;

    public function index(): View
    {
        $rooms = Room::query()->with('branch')->orderBy('name')->paginate(20);

        return view('tenant.academics.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('tenant.academics.rooms.create', $this->academicFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Room::query()->create($data);

        return redirect()->route('tenant.rooms.index')->with('status', __('ui.room_saved'));
    }

    public function edit(Room $room): View
    {
        $this->assertTenantOwned($room);

        return view('tenant.academics.rooms.edit', array_merge($this->academicFormOptions(), compact('room')));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->assertTenantOwned($room);
        $room->update($this->validated($request));

        return redirect()->route('tenant.rooms.index')->with('status', __('ui.room_saved'));
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->assertTenantOwned($room);
        $room->delete();

        return redirect()->route('tenant.rooms.index')->with('status', __('ui.room_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'room_type' => ['required', Rule::in(['classroom', 'lab', 'hall', 'library', 'other'])],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['code'] = ($data['code'] ?? null) ?: null;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Tenant\Communications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Communications\Concerns\LoadsCommunicationsFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\SchoolClass;
use Modules\Communications\Models\Announcement;
use Modules\Communications\Services\AnnouncementService;

class AnnouncementController extends Controller
{
    use LoadsCommunicationsFormOptions;

    public function __construct(protected AnnouncementService $announcements) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $rows = Announcement::query()
            ->with(['schoolClass', 'creator'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', '%'.$q.'%')
                        ->orWhere('body', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '' && in_array($status, Announcement::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.communications.announcements.index', array_merge($this->communicationsFormOptions(), [
            'announcements' => $rows,
            'filterQ' => $q,
            'filterStatus' => $status,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::query()->create($this->payload($request) + [
            'created_by' => $request->user()?->id,
            'status' => Announcement::STATUS_DRAFT,
        ]);

        return back()->with('status', __('ui.announcement_saved'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->assertTenantOwned($announcement);
        $announcement->update($this->payload($request));

        return back()->with('status', __('ui.announcement_saved'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->assertTenantOwned($announcement);
        $announcement->delete();

        return back()->with('status', __('ui.announcement_deleted'));
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->assertTenantOwned($announcement);
        $this->announcements->publish($announcement);

        return back()->with('status', __('ui.announcement_published'));
    }

    public function unpublish(Announcement $announcement): RedirectResponse
    {
        $this->assertTenantOwned($announcement);
        $this->announcements->unpublish($announcement);

        return back()->with('status', __('ui.announcement_unpublished'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience_type' => ['required', Rule::in(Announcement::AUDIENCE_TYPES)],
            'audience_roles' => ['nullable', 'array'],
            'audience_roles.*' => ['string', 'max:125'],
            'school_class_id' => ['nullable', 'integer'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if ($data['audience_type'] === Announcement::AUDIENCE_ROLES) {
            $roles = array_values(array_filter($data['audience_roles'] ?? []));
            abort_if($roles === [], 422);
            $data['audience_roles'] = $roles;
            $data['school_class_id'] = null;
        } elseif ($data['audience_type'] === Announcement::AUDIENCE_CLASS) {
            $classId = $data['school_class_id'] ?? null;
            abort_if($classId === null, 422);
            $this->assertTenantOwned(SchoolClass::query()->findOrFail($classId));
            $data['audience_roles'] = null;
        } else {
            $data['audience_roles'] = null;
            $data['school_class_id'] = null;
        }

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Tenant\Hifz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hifz\Concerns\LoadsHifzFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Services\TeacherHifzScope;

class HifzProfileController extends Controller
{
    use LoadsHifzFormOptions;

    public function index(Request $request, TeacherHifzScope $scope): View
    {
        $options = $this->hifzFormOptions($request->user());
        $query = HifzProfile::query()
            ->with(['student', 'assignedTeacher'])
            ->withCount(['progressLogs', 'revisions'])
            ->orderByDesc('updated_at');

        $scope->constrainProfiles($query, $request->user());

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('teacher_id')) {
            $query->where('assigned_teacher_id', $request->integer('teacher_id'));
        }
        if ($request->filled('band')) {
            $band = HifzProfile::progressBands()[$request->string('band')->toString()] ?? null;
            if ($band) {
                $query->whereBetween('current_juz', [$band['min'], $band['max']]);
            }
        }

        return view('tenant.hifz.profiles.index', array_merge($options, [
            'profiles' => $query->paginate(20)->withQueryString(),
            'filterStatus' => $request->string('status')->toString(),
            'filterTeacherId' => $request->integer('teacher_id') ?: null,
            'filterBand' => $request->string('band')->toString(),
        ]));
    }

    public function create(): View
    {
        return view('tenant.hifz.profiles.create', $this->hifzFormOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->profilePayload($request);
        $data['created_by'] = $request->user()?->id;

        $profile = HifzProfile::query()->create($data);

        return redirect()
            ->route('tenant.hifz.profiles.show', $profile)
            ->with('status', __('ui.hifz_profile_saved'));
    }

    public function show(HifzProfile $profile, TeacherHifzScope $scope): View
    {
        $this->assertTenantOwned($profile);
        abort_unless($scope->canAccessProfile(request()->user(), $profile), 403);

        $profile->load([
            'student',
            'assignedTeacher',
            'progressLogs' => fn ($q) => $q->limit(20),
            'revisions' => fn ($q) => $q->limit(20),
        ]);

        return view('tenant.hifz.profiles.show', array_merge($this->hifzFormOptions(), [
            'profile' => $profile,
        ]));
    }

    public function edit(HifzProfile $profile, TeacherHifzScope $scope): View
    {
        $this->assertTenantOwned($profile);
        abort_unless($scope->canAccessProfile(request()->user(), $profile), 403);

        return view('tenant.hifz.profiles.edit', array_merge($this->hifzFormOptions(), [
            'profile' => $profile,
        ]));
    }

    public function update(Request $request, HifzProfile $profile, TeacherHifzScope $scope): RedirectResponse
    {
        $this->assertTenantOwned($profile);
        abort_unless($scope->canAccessProfile($request->user(), $profile), 403);

        // Scoped teachers may update position/notes but not reassign student/teacher ownership.
        if ($scope->isScoped($request->user())) {
            $data = $request->validate([
                'status' => ['required', Rule::in(HifzProfile::STATUSES)],
                'current_juz' => ['required', 'integer', 'min:1', 'max:30'],
                'current_ayah' => ['required', 'integer', 'min:1', 'max:300'],
                'notes' => ['nullable', 'string'],
            ]);
            $profile->update($data);
        } else {
            $profile->update($this->profilePayload($request, $profile));
        }

        return redirect()
            ->route('tenant.hifz.profiles.show', $profile)
            ->with('status', __('ui.hifz_profile_saved'));
    }

    public function destroy(HifzProfile $profile): RedirectResponse
    {
        $this->assertTenantOwned($profile);
        $profile->delete();

        return redirect()
            ->route('tenant.hifz.profiles.index')
            ->with('status', __('ui.hifz_profile_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(Request $request, ?HifzProfile $existing = null): array
    {
        return $request->validate([
            'student_id' => [
                $existing ? 'sometimes' : 'required',
                'integer',
                Rule::exists('students', 'id'),
                Rule::unique('hifz_profiles', 'student_id')
                    ->where(fn ($q) => $q->where('tenant_id', app(\App\Core\Tenancy\CurrentTenant::class)->id()))
                    ->ignore($existing?->id),
            ],
            'status' => ['required', Rule::in(HifzProfile::STATUSES)],
            'current_juz' => ['required', 'integer', 'min:1', 'max:30'],
            'current_ayah' => ['required', 'integer', 'min:1', 'max:300'],
            'assigned_teacher_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'started_on' => ['nullable', 'date'],
            'completed_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}

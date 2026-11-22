<?php

namespace App\Http\Controllers\Tenant\Hifz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hifz\Concerns\LoadsHifzFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzRevision;
use Modules\Hifz\Services\TeacherHifzScope;

class HifzRevisionController extends Controller
{
    use LoadsHifzFormOptions;

    public function index(Request $request, TeacherHifzScope $scope): View
    {
        $options = $this->hifzFormOptions($request->user());

        $profilesQuery = HifzProfile::query()->with('student')->orderBy('id');
        $scope->constrainProfiles($profilesQuery, $request->user());
        $profiles = $profilesQuery->get();

        $query = HifzRevision::query()
            ->with(['profile.student', 'assignedTeacher'])
            ->orderByDesc('scheduled_on')
            ->orderByDesc('id');

        if ($scope->isScoped($request->user())) {
            $staff = $scope->staffFor($request->user());
            $query->where(function ($q) use ($staff, $profiles) {
                $q->whereIn('hifz_profile_id', $profiles->pluck('id')->all() ?: [0]);
                if ($staff) {
                    $q->orWhere('assigned_teacher_id', $staff->id);
                }
            });
        }

        if ($request->filled('hifz_profile_id')) {
            $profile = HifzProfile::query()->findOrFail($request->integer('hifz_profile_id'));
            $this->assertTenantOwned($profile);
            abort_unless($scope->canAccessProfile($request->user(), $profile), 403);
            $query->where('hifz_profile_id', $profile->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('tenant.hifz.revisions.index', array_merge($options, [
            'profiles' => $profiles,
            'revisions' => $query->paginate(25)->withQueryString(),
            'filterProfileId' => $request->integer('hifz_profile_id') ?: null,
            'filterStatus' => $request->string('status')->toString(),
        ]));
    }

    public function store(Request $request, TeacherHifzScope $scope): RedirectResponse
    {
        $data = $this->revisionPayload($request);

        $profile = HifzProfile::query()->findOrFail($data['hifz_profile_id']);
        $this->assertTenantOwned($profile);
        abort_unless($scope->canAccessProfile($request->user(), $profile), 403);

        if ($scope->isScoped($request->user())) {
            $staff = $scope->staffFor($request->user());
            $data['assigned_teacher_id'] = $staff?->id ?? $profile->assigned_teacher_id;
        }

        $data['created_by'] = $request->user()?->id;
        HifzRevision::query()->create($data);

        return redirect()
            ->route('tenant.hifz.revisions.index', ['hifz_profile_id' => $profile->id])
            ->with('status', __('ui.hifz_revision_saved'));
    }

    public function update(Request $request, HifzRevision $revision, TeacherHifzScope $scope): RedirectResponse
    {
        $this->assertTenantOwned($revision);
        $profile = $revision->profile;
        abort_unless($profile && $scope->canAccessProfile($request->user(), $profile), 403);

        $data = $this->revisionPayload($request, updating: true);
        if ($scope->isScoped($request->user())) {
            unset($data['hifz_profile_id'], $data['assigned_teacher_id']);
        }

        if (($data['status'] ?? null) === HifzRevision::STATUS_COMPLETED && empty($data['completed_on'])) {
            $data['completed_on'] = now('Asia/Dhaka')->toDateString();
        }

        $revision->update($data);

        return redirect()
            ->route('tenant.hifz.revisions.index', ['hifz_profile_id' => $revision->hifz_profile_id])
            ->with('status', __('ui.hifz_revision_saved'));
    }

    public function destroy(HifzRevision $revision, TeacherHifzScope $scope): RedirectResponse
    {
        $this->assertTenantOwned($revision);
        $profile = $revision->profile;
        abort_unless($profile && $scope->canAccessProfile(request()->user(), $profile), 403);

        $profileId = $revision->hifz_profile_id;
        $revision->delete();

        return redirect()
            ->route('tenant.hifz.revisions.index', ['hifz_profile_id' => $profileId])
            ->with('status', __('ui.hifz_revision_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function revisionPayload(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'hifz_profile_id' => [$updating ? 'sometimes' : 'required', 'integer', Rule::exists('hifz_profiles', 'id')],
            'label' => ['nullable', 'string', 'max:255'],
            'from_juz' => ['required', 'integer', 'min:1', 'max:30'],
            'to_juz' => ['required', 'integer', 'min:1', 'max:30', 'gte:from_juz'],
            'scheduled_on' => ['nullable', 'date'],
            'completed_on' => ['nullable', 'date'],
            'status' => ['required', Rule::in(HifzRevision::STATUSES)],
            'assigned_teacher_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'notes' => ['nullable', 'string'],
        ]);
    }
}

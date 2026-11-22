<?php

namespace App\Http\Controllers\Tenant\Hifz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hifz\Concerns\LoadsHifzFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzProgressLog;
use Modules\Hifz\Services\TeacherHifzScope;

class HifzProgressLogController extends Controller
{
    use LoadsHifzFormOptions;

    public function index(Request $request, TeacherHifzScope $scope): View
    {
        $options = $this->hifzFormOptions($request->user());

        $profilesQuery = HifzProfile::query()->with('student')->orderBy('id');
        $scope->constrainProfiles($profilesQuery, $request->user());
        $profiles = $profilesQuery->get();

        $profileId = $request->integer('hifz_profile_id') ?: ($profiles->first()?->id);
        $profile = $profileId
            ? HifzProfile::query()->with(['student', 'assignedTeacher'])->find($profileId)
            : null;

        if ($profile) {
            $this->assertTenantOwned($profile);
            abort_unless($scope->canAccessProfile($request->user(), $profile), 403);
        }

        $logs = $profile
            ? HifzProgressLog::query()
                ->where('hifz_profile_id', $profile->id)
                ->orderByDesc('logged_on')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString()
            : collect();

        return view('tenant.hifz.progress.index', array_merge($options, [
            'profiles' => $profiles,
            'profile' => $profile,
            'logs' => $logs,
        ]));
    }

    public function store(Request $request, TeacherHifzScope $scope): RedirectResponse
    {
        $data = $request->validate([
            'hifz_profile_id' => ['required', 'integer', Rule::exists('hifz_profiles', 'id')],
            'logged_on' => ['required', 'date'],
            'from_juz' => ['required', 'integer', 'min:1', 'max:30'],
            'from_ayah' => ['required', 'integer', 'min:1', 'max:300'],
            'to_juz' => ['required', 'integer', 'min:1', 'max:30'],
            'to_ayah' => ['required', 'integer', 'min:1', 'max:300'],
            'quality' => ['nullable', Rule::in(HifzProgressLog::QUALITIES)],
            'teacher_notes' => ['nullable', 'string'],
            'update_profile_position' => ['nullable', 'boolean'],
        ]);

        $profile = HifzProfile::query()->findOrFail($data['hifz_profile_id']);
        $this->assertTenantOwned($profile);
        abort_unless($scope->canAccessProfile($request->user(), $profile), 403);

        $data['logged_by'] = $request->user()?->id;
        unset($data['update_profile_position']);

        HifzProgressLog::query()->create($data);

        if ($request->boolean('update_profile_position')) {
            $profile->update([
                'current_juz' => $data['to_juz'],
                'current_ayah' => $data['to_ayah'],
            ]);
        }

        return redirect()
            ->route('tenant.hifz.progress.index', ['hifz_profile_id' => $profile->id])
            ->with('status', __('ui.hifz_progress_saved'));
    }

    public function destroy(HifzProgressLog $log, TeacherHifzScope $scope): RedirectResponse
    {
        $this->assertTenantOwned($log);
        $profile = $log->profile;
        abort_unless($profile && $scope->canAccessProfile(request()->user(), $profile), 403);

        $profileId = $log->hifz_profile_id;
        $log->delete();

        return redirect()
            ->route('tenant.hifz.progress.index', ['hifz_profile_id' => $profileId])
            ->with('status', __('ui.hifz_progress_deleted'));
    }
}

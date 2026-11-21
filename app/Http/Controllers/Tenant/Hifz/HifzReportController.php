<?php

namespace App\Http\Controllers\Tenant\Hifz;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Hifz\Concerns\LoadsHifzFormOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzRevision;
use Modules\Hifz\Services\TeacherHifzScope;
use Modules\Hr\Models\Staff;
use Modules\Tenancy\Models\Institution;

class HifzReportController extends Controller
{
    use LoadsHifzFormOptions;

    public function index(Request $request, TeacherHifzScope $scope): View
    {
        $summary = $this->buildSummary($request->user(), $scope);

        return view('tenant.hifz.reports.index', array_merge($this->hifzFormOptions($request->user()), [
            'summary' => $summary,
        ]));
    }

    public function pdf(Request $request, TeacherHifzScope $scope): Response
    {
        $summary = $this->buildSummary($request->user(), $scope);
        $institution = Institution::query()->first();

        $pdf = Pdf::loadView('tenant.hifz.pdf.muhtamim-report', [
            'summary' => $summary,
            'institution' => $institution,
            'generatedAt' => now('Asia/Dhaka'),
            'fontNote' => __('ui.pdf_bangla_font_note'),
        ])->setPaper('a4');

        return $pdf->download('hifz-muhtamim-report.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSummary($user, TeacherHifzScope $scope): array
    {
        $profilesQuery = HifzProfile::query()->with(['student', 'assignedTeacher']);
        $scope->constrainProfiles($profilesQuery, $user);
        $profiles = $profilesQuery->get();

        $bands = HifzProfile::progressBands();
        $byBand = [];
        foreach ($bands as $key => $band) {
            $byBand[$key] = [
                'label' => $band['label'],
                'count' => $profiles->filter(
                    fn (HifzProfile $p) => $p->current_juz >= $band['min'] && $p->current_juz <= $band['max']
                )->count(),
            ];
        }

        $byStatus = [];
        foreach (HifzProfile::STATUSES as $status) {
            $byStatus[$status] = $profiles->where('status', $status)->count();
        }

        $teacherLoad = $profiles
            ->groupBy('assigned_teacher_id')
            ->map(function ($group, $teacherId) {
                /** @var \Modules\Hifz\Models\HifzProfile|null $first */
                $first = $group->first();
                $teacher = $first?->assignedTeacher;

                return [
                    'teacher_id' => $teacherId ?: null,
                    'name' => $teacher?->full_name ?? __('ui.unassigned'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        $recentRevisions = HifzRevision::query()
            ->with(['profile.student', 'assignedTeacher'])
            ->whereIn('hifz_profile_id', $profiles->pluck('id')->all() ?: [0])
            ->orderByDesc('scheduled_on')
            ->limit(15)
            ->get();

        $teachersCount = Staff::query()->teachers()->where('status', Staff::STATUS_ACTIVE)->count();

        return [
            'total_profiles' => $profiles->count(),
            'by_band' => $byBand,
            'by_status' => $byStatus,
            'teacher_load' => $teacherLoad,
            'recent_revisions' => $recentRevisions,
            'teachers_count' => $teachersCount,
            'profiles' => $profiles->take(40),
        ];
    }
}

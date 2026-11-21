<?php

namespace Modules\Hifz\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\Subject;
use Modules\Hifz\Models\HifzProfile;
use Modules\Hifz\Models\HifzProgressLog;
use Modules\Hifz\Models\HifzRevision;
use Modules\Hifz\Models\IslamicSubjectMeta;
use Modules\Hr\Models\Staff;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo Hifz profiles, progress logs, dawr revisions, and Islamic subject meta.
 */
class DemoHifzProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $students = Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('id')
                ->limit(6)
                ->get();

            $teachers = Staff::query()
                ->teachers()
                ->where('status', Staff::STATUS_ACTIVE)
                ->orderBy('id')
                ->limit(3)
                ->get();

            if ($students->isEmpty()) {
                return;
            }

            $teacherIds = $teachers->pluck('id')->all();
            $profiles = [];

            foreach ($students as $i => $student) {
                $juz = match ($i) {
                    0 => 3,
                    1 => 8,
                    2 => 14,
                    3 => 22,
                    4 => 28,
                    default => 5,
                };
                $status = $i === 5 ? HifzProfile::STATUS_PAUSED : HifzProfile::STATUS_ACTIVE;
                if ($i === 4) {
                    $status = HifzProfile::STATUS_COMPLETED;
                }

                $profiles[] = HifzProfile::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'current_juz' => $juz,
                        'current_ayah' => 1 + ($i % 7),
                        'assigned_teacher_id' => $teacherIds[$i % max(1, count($teacherIds))] ?? null,
                        'started_on' => now('Asia/Dhaka')->subMonths(6 - $i)->toDateString(),
                        'completed_on' => $status === HifzProfile::STATUS_COMPLETED
                            ? now('Asia/Dhaka')->subWeek()->toDateString()
                            : null,
                        'notes' => $i === 0
                            ? 'Demo Hifz profile — Nazira early band for Try Live.'
                            : null,
                    ]
                );
            }

            foreach ($profiles as $i => $profile) {
                $fromJuz = max(1, (int) $profile->current_juz - 1);
                HifzProgressLog::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'hifz_profile_id' => $profile->id,
                        'logged_on' => now('Asia/Dhaka')->subDays(2 + $i)->toDateString(),
                        'from_juz' => $fromJuz,
                        'from_ayah' => 1,
                        'to_juz' => (int) $profile->current_juz,
                        'to_ayah' => (int) $profile->current_ayah,
                    ],
                    [
                        'quality' => HifzProgressLog::QUALITIES[$i % count(HifzProgressLog::QUALITIES)],
                        'teacher_notes' => 'Demo sabak progress entry.',
                    ]
                );

                HifzRevision::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'hifz_profile_id' => $profile->id,
                        'label' => 'Dawr cycle '.($i + 1),
                    ],
                    [
                        'from_juz' => max(1, (int) $profile->current_juz - 2),
                        'to_juz' => (int) $profile->current_juz,
                        'scheduled_on' => now('Asia/Dhaka')->subDays($i)->toDateString(),
                        'completed_on' => $i % 2 === 0
                            ? now('Asia/Dhaka')->subDays($i)->toDateString()
                            : null,
                        'status' => $i % 2 === 0
                            ? HifzRevision::STATUS_COMPLETED
                            : HifzRevision::STATUS_SCHEDULED,
                        'assigned_teacher_id' => $profile->assigned_teacher_id,
                        'notes' => 'Demo dawr / revision cycle.',
                    ]
                );
            }

            $subjects = Subject::query()
                ->whereIn('subject_type', ['islamic', 'hifz'])
                ->orderBy('id')
                ->limit(4)
                ->get();

            if ($subjects->isEmpty()) {
                $subjects = Subject::query()->orderBy('id')->limit(2)->get();
            }

            foreach ($subjects as $i => $subject) {
                IslamicSubjectMeta::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'kitab_name' => $i === 0 ? 'Nurani Qaida' : ($subject->name.' Kitab'),
                        'department_label' => $subject->subject_type === 'hifz' ? 'Hifz Circle' : 'Qawmi / Islamic',
                        'qawmi_level' => $i < 2 ? 'Ibtedayee' : 'Mutawassit',
                        'meta' => [
                            'track' => $subject->subject_type ?? 'islamic',
                            'demo' => true,
                        ],
                        'notes' => 'Light extensible Islamic/Qawmi subject metadata.',
                    ]
                );
            }
        });
    }
}

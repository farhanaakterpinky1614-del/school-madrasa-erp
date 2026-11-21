<?php

namespace Modules\Examinations\Services;

use Modules\Academics\Models\GradingScaleBand;
use Modules\Examinations\Models\Exam;
use Modules\Examinations\Models\ExamSubject;

class GradeResolver
{
    /**
     * @return array{grade: string|null, gpa: float|null}
     */
    public function resolve(Exam $exam, ExamSubject $examSubject, ?float $marksObtained, bool $isAbsent = false): array
    {
        if ($isAbsent || $marksObtained === null) {
            return ['grade' => $isAbsent ? 'Abs' : null, 'gpa' => null];
        }

        $full = (float) $examSubject->full_marks;
        $percent = $full > 0 ? ($marksObtained / $full) * 100 : 0.0;

        $scale = $exam->gradingScheme?->gradingScale;
        if ($scale === null) {
            $exam->loadMissing('gradingScheme.gradingScale.bands');
            $scale = $exam->gradingScheme?->gradingScale;
        }

        if ($scale === null) {
            return ['grade' => null, 'gpa' => null];
        }

        $bands = $scale->relationLoaded('bands')
            ? $scale->bands
            : $scale->bands()->get();

        /** @var GradingScaleBand|null $band */
        $band = $bands->first(function (GradingScaleBand $b) use ($percent) {
            return $percent >= (float) $b->min_mark && $percent <= (float) $b->max_mark;
        });

        if ($band === null) {
            // Edge: exactly above max of lower band — pick nearest by min desc
            $band = $bands->sortByDesc('min_mark')->first(
                fn (GradingScaleBand $b) => $percent >= (float) $b->min_mark
            );
        }

        return [
            'grade' => $band?->grade,
            'gpa' => $band?->gpa !== null ? (float) $band->gpa : null,
        ];
    }
}

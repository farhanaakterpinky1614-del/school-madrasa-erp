<?php

namespace App\Http\Controllers\Tenant\Academics\Concerns;

use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\Branch;
use Modules\Academics\Models\Curriculum;
use Modules\Academics\Models\Period;
use Modules\Academics\Models\Room;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Section;
use Modules\Academics\Models\Shift;
use Modules\Academics\Models\Subject;
use Modules\Academics\Models\SyllabusTopic;
use Modules\Hr\Models\Staff;

trait LoadsAcademicFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function academicFormOptions(bool $withTopics = false): array
    {
        $options = [
            'years' => AcademicYear::query()->orderByDesc('starts_on')->get(),
            'classes' => SchoolClass::query()->orderBy('numeric_level')->get(),
            'sections' => Section::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'teachers' => Staff::query()->teachers()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get(),
            'rooms' => Room::query()->where('is_active', true)->orderBy('name')->get(),
            'periods' => Period::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
            'curricula' => Curriculum::query()->orderBy('name')->get(),
            'days' => [
                0 => __('ui.day_sun'),
                1 => __('ui.day_mon'),
                2 => __('ui.day_tue'),
                3 => __('ui.day_wed'),
                4 => __('ui.day_thu'),
                5 => __('ui.day_fri'),
                6 => __('ui.day_sat'),
            ],
        ];

        if ($withTopics) {
            $options['topics'] = SyllabusTopic::query()->with('syllabus')->orderBy('sort_order')->get();
        }

        return $options;
    }
}

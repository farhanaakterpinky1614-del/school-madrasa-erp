<?php

namespace App\Http\Controllers\Tenant\Academics;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\ClassworkAssignment;
use Modules\Academics\Models\HomeworkItem;
use Modules\Academics\Models\LearningMaterial;
use Modules\Academics\Models\LessonPlan;
use Modules\Academics\Models\Period;
use Modules\Academics\Models\Room;
use Modules\Academics\Models\Subject;
use Modules\Academics\Models\Syllabus;

/**
 * Academic reports foundation — summary counts for ops overview.
 */
class AcademicReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'subjects' => Subject::query()->where('is_active', true)->count(),
            'rooms' => Room::query()->where('is_active', true)->count(),
            'periods' => Period::query()->where('is_active', true)->count(),
            'routines' => ClassRoutine::query()->where('is_active', true)->count(),
            'syllabi' => Syllabus::query()->where('is_active', true)->count(),
            'lesson_plans' => LessonPlan::query()->count(),
            'lesson_plans_published' => LessonPlan::query()->where('status', LessonPlan::STATUS_PUBLISHED)->count(),
            'homework' => HomeworkItem::query()->count(),
            'homework_open' => HomeworkItem::query()->where('status', HomeworkItem::STATUS_PUBLISHED)->count(),
            'classwork' => ClassworkAssignment::query()->count(),
            'materials' => LearningMaterial::query()->where('is_published', true)->count(),
        ];

        $upcomingHomework = HomeworkItem::query()
            ->with(['schoolClass', 'subject'])
            ->where('status', HomeworkItem::STATUS_PUBLISHED)
            ->whereNotNull('due_on')
            ->orderBy('due_on')
            ->limit(8)
            ->get();

        $recentPlans = LessonPlan::query()
            ->with(['schoolClass', 'subject'])
            ->orderByDesc('planned_on')
            ->limit(8)
            ->get();

        return view('tenant.academics.reports.index', compact('stats', 'upcomingHomework', 'recentPlans'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Academics\Models\AcademicYear;
use Modules\Attendance\Models\StudentAttendance;
use Modules\Integrations\Models\WebhookSubscription;
use Modules\Integrations\Services\WebhookDispatcher;
use Modules\Students\Models\Student;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'attendance.daily.view');

        $query = StudentAttendance::query()->with('student:id,first_name,last_name,student_code')
            ->orderByDesc('attendance_date')
            ->orderBy('id');

        if ($date = $request->string('date')->toString()) {
            $query->whereDate('attendance_date', Carbon::parse($date)->toDateString());
        }

        if ($classId = $request->integer('school_class_id')) {
            $query->where('school_class_id', $classId);
        }

        $rows = $query->paginate(min(200, max(1, (int) $request->integer('per_page', 50))));

        return response()->json($rows);
    }

    public function summary(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'attendance.daily.view');

        $date = Carbon::parse($request->string('date')->toString() ?: now('Asia/Dhaka')->toDateString())
            ->toDateString();

        $counts = StudentAttendance::query()
            ->whereDate('attendance_date', $date)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'date' => $date,
            'counts' => $counts,
            'marked' => (int) $counts->sum(),
        ]);
    }

    public function store(Request $request, CurrentTenant $currentTenant, WebhookDispatcher $webhooks): JsonResponse
    {
        $this->authorizePermission($request, 'attendance.daily.manage');
        $tenant = $currentTenant->require();

        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'entries.*.status' => ['required', Rule::in(StudentAttendance::STATUSES)],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $attendanceDate = Carbon::parse($data['attendance_date'], 'Asia/Dhaka')->toDateString();
        $yearId = $data['academic_year_id']
            ?? AcademicYear::query()->where('is_current', true)->value('id');

        $saved = [];

        foreach ($data['entries'] as $entry) {
            $student = Student::query()->findOrFail($entry['student_id']);
            abort_unless((int) $student->tenant_id === (int) $tenant->id, 404);

            $values = [
                'academic_year_id' => $yearId,
                'school_class_id' => (int) $data['school_class_id'],
                'section_id' => isset($data['section_id']) ? (int) $data['section_id'] : null,
                'status' => $entry['status'],
                'notes' => $entry['notes'] ?? null,
                'marked_by' => $request->user()?->id,
            ];

            $record = StudentAttendance::query()
                ->where('tenant_id', $tenant->id)
                ->where('student_id', $student->id)
                ->whereDate('attendance_date', $attendanceDate)
                ->first();

            if ($record) {
                $record->update($values);
            } else {
                $record = StudentAttendance::query()->create([
                    'tenant_id' => $tenant->id,
                    'student_id' => $student->id,
                    'attendance_date' => $attendanceDate,
                    ...$values,
                ]);
            }

            $saved[] = $record->fresh();
        }

        $webhooks->dispatch($tenant, WebhookSubscription::EVENT_ATTENDANCE_MARKED, [
            'attendance_date' => $attendanceDate,
            'school_class_id' => (int) $data['school_class_id'],
            'section_id' => $data['section_id'] ?? null,
            'marked_count' => count($saved),
            'entries' => collect($saved)->map(fn ($r) => [
                'student_id' => $r->student_id,
                'status' => $r->status,
            ])->values()->all(),
        ]);

        return response()->json(['data' => $saved, 'count' => count($saved)], 201);
    }

    protected function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->can($permission), 403, 'Missing permission: '.$permission);
    }
}

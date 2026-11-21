<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Hr\Models\Staff;
use Modules\Hr\Models\StaffAttendance;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->string('date')->toString() ?: now('Asia/Dhaka')->toDateString();
        $staff = Staff::query()->where('status', Staff::STATUS_ACTIVE)->orderBy('first_name')->get();
        $existing = StaffAttendance::query()
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('staff_id');

        return view('tenant.hr.attendance.index', compact('staff', 'existing', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'entries' => ['required', 'array'],
            'entries.*.staff_id' => ['required', 'integer', Rule::exists('staff', 'id')],
            'entries.*.status' => ['required', Rule::in([
                StaffAttendance::STATUS_PRESENT,
                StaffAttendance::STATUS_ABSENT,
                StaffAttendance::STATUS_LATE,
                StaffAttendance::STATUS_HALF_DAY,
                StaffAttendance::STATUS_ON_LEAVE,
            ])],
            'entries.*.check_in' => ['nullable', 'date_format:H:i'],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['entries'] as $entry) {
            $member = Staff::query()->findOrFail($entry['staff_id']);
            $this->assertTenantOwned($member);

            StaffAttendance::query()->updateOrCreate(
                [
                    'staff_id' => $member->id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'status' => $entry['status'],
                    'check_in' => $entry['check_in'] ?? null,
                    'notes' => $entry['notes'] ?? null,
                    'marked_by' => $request->user()?->id,
                ]
            );
        }

        return redirect()
            ->route('tenant.staff-attendance.index', ['date' => $data['attendance_date']])
            ->with('status', __('ui.attendance_saved'));
    }
}

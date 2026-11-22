<?php

namespace App\Http\Controllers\Tenant\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Attendance\Concerns\LoadsAttendanceFormOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\ClassRoutine;
use Modules\Attendance\Models\AttendanceQrToken;
use Modules\Attendance\Services\TeacherAttendanceScope;

/**
 * Light QR / check-in foundation — period token stub (no hardware).
 */
class AttendanceQrController extends Controller
{
    use LoadsAttendanceFormOptions;

    public function index(Request $request): View
    {
        $options = $this->attendanceFormOptions($request->user());
        $date = $request->string('date')->toString() ?: now('Asia/Dhaka')->toDateString();
        $tokens = AttendanceQrToken::query()
            ->with(['classRoutine.schoolClass', 'classRoutine.period', 'classRoutine.subject'])
            ->whereDate('valid_on', $date)
            ->orderByDesc('id')
            ->get();

        return view('tenant.attendance.qr.index', array_merge($options, [
            'date' => $date,
            'tokens' => $tokens,
        ]));
    }

    public function store(Request $request, TeacherAttendanceScope $scope): RedirectResponse
    {
        $data = $request->validate([
            'class_routine_id' => ['required', 'integer', Rule::exists('class_routines', 'id')],
            'valid_on' => ['required', 'date'],
        ]);

        $routine = ClassRoutine::query()->findOrFail($data['class_routine_id']);
        $this->assertTenantOwned($routine);
        abort_unless($scope->canMarkRoutine($request->user(), $routine), 403);

        $token = AttendanceQrToken::query()->updateOrCreate(
            [
                'class_routine_id' => $routine->id,
                'valid_on' => $data['valid_on'],
            ],
            [
                'token' => Str::lower(Str::random(32)),
                'expires_at' => Carbon::parse($data['valid_on'], 'Asia/Dhaka')->endOfDay(),
                'created_by' => $request->user()?->id,
            ]
        );

        return redirect()
            ->route('tenant.attendance.qr.index', ['date' => $data['valid_on']])
            ->with('status', __('ui.attendance_qr_created'))
            ->with('qr_token', $token->token);
    }
}

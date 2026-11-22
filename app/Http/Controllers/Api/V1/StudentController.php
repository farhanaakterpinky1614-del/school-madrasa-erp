<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenancy\CurrentTenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Platform\Models\TenantUsageMetric;
use Modules\Platform\Services\UsageTracker;
use Modules\Students\Models\Student;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'students.student.view');

        $query = Student::query()->orderBy('last_name')->orderBy('first_name');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(min(100, max(1, (int) $request->integer('per_page', 25))));

        return response()->json($students);
    }

    public function show(Request $request, Student $student): JsonResponse
    {
        $this->authorizePermission($request, 'students.student.view');
        parent::assertTenantOwned($student);

        return response()->json(['data' => $student]);
    }

    public function store(Request $request, CurrentTenant $currentTenant, UsageTracker $usage): JsonResponse
    {
        $this->authorizePermission($request, 'students.student.create');
        $tenant = $currentTenant->require();

        $usage->assertWithinQuota($tenant, TenantUsageMetric::STUDENTS);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:180'],
            'gender' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:180'],
            'status' => ['nullable', 'in:active,withdrawn,alumni,transferred'],
            'student_code' => ['nullable', 'string', 'max:64'],
            'admission_number' => ['nullable', 'string', 'max:64'],
        ]);

        // Never trust client tenant_id.
        unset($data['tenant_id']);

        $student = Student::query()->create([
            ...$data,
            'status' => $data['status'] ?? Student::STATUS_ACTIVE,
            'enrolled_on' => now('Asia/Dhaka')->toDateString(),
        ]);

        $usage->increment($tenant, TenantUsageMetric::STUDENTS);

        return response()->json(['data' => $student], 201);
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $this->authorizePermission($request, 'students.student.update');
        parent::assertTenantOwned($student);

        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:180'],
            'gender' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:180'],
            'status' => ['nullable', 'in:active,withdrawn,alumni,transferred'],
        ]);

        unset($data['tenant_id']);
        $student->update($data);

        return response()->json(['data' => $student->fresh()]);
    }

    protected function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->can($permission), 403, 'Missing permission: '.$permission);
    }
}

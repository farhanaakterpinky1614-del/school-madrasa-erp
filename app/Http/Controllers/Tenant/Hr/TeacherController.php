<?php

namespace App\Http\Controllers\Tenant\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Hr\Models\Staff;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Staff::query()
            ->teachers()
            ->with(['designation', 'department', 'assignments.schoolClass', 'assignments.subject'])
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('employee_code', 'like', "%{$term}%");
                });
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('tenant.hr.teachers.index', compact('teachers'));
    }
}

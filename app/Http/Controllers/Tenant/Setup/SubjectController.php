<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\Subject;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()->orderBy('name')->paginate(20);

        return view('tenant.setup.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('tenant.setup.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'subject_type' => ['required', Rule::in(['general', 'islamic', 'hifz', 'optional'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        Subject::query()->create($data);

        return redirect()->route('tenant.subjects.index')->with('status', __('ui.subject_saved'));
    }

    public function edit(Subject $subject): View
    {
        $this->assertTenantOwned($subject);
        return view('tenant.setup.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $this->assertTenantOwned($subject);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32'],
            'subject_type' => ['required', Rule::in(['general', 'islamic', 'hifz', 'optional'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('code', $data)) {
            $data['code'] = $data['code'] ?: null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $subject->update($data);

        return redirect()->route('tenant.subjects.index')->with('status', __('ui.subject_saved'));
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->assertTenantOwned($subject);
        $subject->delete();

        return redirect()->route('tenant.subjects.index')->with('status', __('ui.subject_deleted'));
    }
}

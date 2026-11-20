<?php

namespace App\Http\Controllers\Tenant\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\CalendarEvent;
use Modules\Settings\Services\InstitutionSettingsService;
use App\Core\Tenancy\CurrentTenant;

class CalendarEventController extends Controller
{
    public function index(CurrentTenant $currentTenant, InstitutionSettingsService $settings): View
    {
        $events = CalendarEvent::query()->with('academicYear')->orderBy('starts_on')->paginate(20);
        $institutionSettings = $settings->forTenant($currentTenant->require());

        return view('tenant.setup.calendar.index', [
            'events' => $events,
            'workingDays' => $institutionSettings['working_days'] ?? [],
        ]);
    }

    public function create(): View
    {
        $years = AcademicYear::query()->orderByDesc('starts_on')->get();

        return view('tenant.setup.calendar.create', compact('years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in(['holiday', 'working', 'exam', 'event'])],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_recurring' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['is_active'] = $request->boolean('is_active', true);

        CalendarEvent::query()->create($data);

        return redirect()->route('tenant.calendar.index')->with('status', __('ui.calendar_saved'));
    }

    public function edit(CalendarEvent $calendar): View
    {
        $this->assertTenantOwned($calendar);
        $years = AcademicYear::query()->orderByDesc('starts_on')->get();

        return view('tenant.setup.calendar.edit', ['event' => $calendar, 'years' => $years]);
    }

    public function update(Request $request, CalendarEvent $calendar): RedirectResponse
    {
        $this->assertTenantOwned($calendar);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in(['holiday', 'working', 'exam', 'event'])],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_recurring' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['is_active'] = $request->boolean('is_active', true);

        $calendar->update($data);

        return redirect()->route('tenant.calendar.index')->with('status', __('ui.calendar_saved'));
    }

    public function destroy(CalendarEvent $calendar): RedirectResponse
    {
        $this->assertTenantOwned($calendar);
        $calendar->delete();

        return redirect()->route('tenant.calendar.index')->with('status', __('ui.calendar_deleted'));
    }
}

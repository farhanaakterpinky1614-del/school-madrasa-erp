<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Platform\Models\Plan;
use Modules\Tenancy\Services\TenantRegistrar;

class RegisterTenantController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'institutionTypes' => config('sme.institution_types'),
        ]);
    }

    public function store(Request $request, TenantRegistrar $registrar): RedirectResponse
    {
        $data = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'min:3', 'max:63', 'alpha_dash'],
            'institution_type' => ['required', 'string', 'in:'.implode(',', array_keys(config('sme.institution_types')))],
            'plan_code' => ['required', 'string', 'exists:plans,code'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $result = $registrar->register([
            ...$data,
            'locale' => app()->getLocale(),
        ]);

        Auth::login($result['user']);
        $request->session()->regenerate();

        return redirect()
            ->route('tenant.dashboard')
            ->with('status', 'Institution registered. Welcome aboard!');
    }
}

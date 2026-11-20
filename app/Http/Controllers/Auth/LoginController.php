<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Identity\Services\LoginHistoryRecorder;
use Modules\Tenancy\Models\Tenant;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, LoginHistoryRecorder $history): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 60);
            $history->failed($credentials['email']);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        /** @var User $user */
        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            $history->failed($credentials['email'], $user->tenant_id);
            throw ValidationException::withMessages([
                'email' => __('This account is inactive.'),
            ]);
        }

        if (! $user->is_platform_admin && $user->tenant_id) {
            $tenant = Tenant::query()->find($user->tenant_id);
            if ($tenant && $tenant->status === Tenant::STATUS_SUSPENDED) {
                Auth::logout();
                $history->failed($credentials['email'], $user->tenant_id);
                throw ValidationException::withMessages([
                    'email' => __('This institution is suspended. Contact support.'),
                ]);
            }
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        $history->success($user);

        $default = $user->is_platform_admin
            ? route('platform.dashboard')
            : route('tenant.dashboard');

        // Avoid sending platform admins to /app|/dashboard bookmarks (was a Try Live 403).
        $intended = $request->session()->pull('url.intended', $default);
        if ($user->is_platform_admin && $this->isTenantBackOfficeUrl($intended)) {
            return redirect()->to($default);
        }

        return redirect()->to($intended ?: $default);
    }

    protected function isTenantBackOfficeUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        return $path === '/app'
            || str_starts_with($path, '/app/')
            || $path === '/dashboard';
    }

    public function destroy(Request $request, LoginHistoryRecorder $history): RedirectResponse
    {
        if ($request->user()) {
            $history->logout($request->user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $max = (int) config('identity.rate_limits.login', 5);

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $max)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')->toString()).'|'.$request->ip());
    }
}

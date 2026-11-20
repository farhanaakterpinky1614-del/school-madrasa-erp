<?php

namespace App\Providers;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Academics\Models\AcademicTerm;
use Modules\Academics\Models\AcademicYear;
use Modules\Academics\Models\CalendarEvent;
use Modules\Academics\Models\CurriculumSubject;
use Modules\Academics\Models\GradingScale;
use Modules\Academics\Models\GradingScaleBand;
use Modules\Academics\Models\SchoolClass;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentTenant::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Route::bind('year', fn (string $value) => AcademicYear::query()->whereKey($value)->firstOrFail());
        Route::bind('term', fn (string $value) => AcademicTerm::query()->whereKey($value)->firstOrFail());
        Route::bind('class', fn (string $value) => SchoolClass::query()->whereKey($value)->firstOrFail());
        Route::bind('grading', fn (string $value) => GradingScale::query()->whereKey($value)->firstOrFail());
        Route::bind('band', fn (string $value) => GradingScaleBand::query()->whereKey($value)->firstOrFail());
        Route::bind('calendar', fn (string $value) => CalendarEvent::query()->whereKey($value)->firstOrFail());
        Route::bind('link', fn (string $value) => CurriculumSubject::query()->whereKey($value)->firstOrFail());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute((int) config('identity.rate_limits.login', 5))
                ->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute((int) config('identity.rate_limits.password_reset', 3))
                ->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}

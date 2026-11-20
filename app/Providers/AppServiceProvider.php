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
use Illuminate\Support\ServiceProvider;
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

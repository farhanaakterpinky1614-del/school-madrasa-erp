<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterTenantController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Platform\AuditLogController as PlatformAuditLogController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\InstitutionController as PlatformInstitutionController;
use App\Http\Controllers\Platform\ModuleController as PlatformModuleController;
use App\Http\Controllers\Platform\PlanController as PlatformPlanController;
use App\Http\Controllers\Platform\TenantController as PlatformTenantController;
use App\Http\Controllers\Platform\UsageController as PlatformUsageController;
use App\Http\Controllers\Platform\UserController as PlatformUserController;
use App\Http\Controllers\Tenant\AuditLogController as TenantAuditLogController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\DomainController as TenantDomainController;
use App\Http\Controllers\Tenant\InstitutionController as TenantInstitutionController;
use App\Http\Controllers\Tenant\LoginHistoryController as TenantLoginHistoryController;
use App\Http\Controllers\Tenant\MembershipController as TenantMembershipController;
use App\Http\Controllers\Tenant\ModuleController as TenantModuleController;
use App\Http\Controllers\Tenant\PermissionController as TenantPermissionController;
use App\Http\Controllers\Tenant\RoleController as TenantRoleController;
use App\Http\Controllers\Tenant\SessionController as TenantSessionController;
use App\Http\Controllers\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\Tenant\UsageController as TenantUsageController;
use App\Http\Controllers\Tenant\UserController as TenantUserController;
use App\Livewire\Demo\UiKitchenSink;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->is_platform_admin
            ? redirect()->route('platform.dashboard')
            : redirect()->route('tenant.dashboard');
    }

    return redirect()->route('login');
});

Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [RegisterTenantController::class, 'create'])->name('register');
    Route::post('/register', [RegisterTenantController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Design-system demos (Phase 01) — auth optional for kitchen sink
|--------------------------------------------------------------------------
*/
Route::view('/ui/shell-preview', 'dashboard.index')->name('ui.shell-preview');
Route::get('/ui/kitchen-sink', UiKitchenSink::class)->name('ui.kitchen-sink');

/*
|--------------------------------------------------------------------------
| Super Admin (platform) — no tenant scope required
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'platform.admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {
        Route::get('/', PlatformDashboardController::class)->name('dashboard');
        Route::get('/tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [PlatformTenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [PlatformTenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [PlatformTenantController::class, 'show'])->name('tenants.show');
        Route::patch('/tenants/{tenant}/status', [PlatformTenantController::class, 'updateStatus'])->name('tenants.status');

        Route::get('/institutions', [PlatformInstitutionController::class, 'index'])->name('institutions.index');
        Route::get('/institutions/{institution}', [PlatformInstitutionController::class, 'show'])->name('institutions.show');

        Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
        Route::get('/modules', [PlatformModuleController::class, 'index'])->name('modules.index');
        Route::get('/usage', [PlatformUsageController::class, 'index'])->name('usage.index');

        Route::get('/users', [PlatformUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [PlatformUserController::class, 'create'])->name('users.create');
        Route::post('/users', [PlatformUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [PlatformUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [PlatformUserController::class, 'update'])->name('users.update');

        Route::get('/audit', [PlatformAuditLogController::class, 'index'])->name('audit.index');
    });

/*
|--------------------------------------------------------------------------
| Tenant back-office — members + permission gates (server-side)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant.member', 'tenant.context'])
    ->prefix('app')
    ->name('tenant.')
    ->group(function () {
        Route::get('/', TenantDashboardController::class)->name('dashboard');

        Route::middleware('permission:settings.institution.view')->group(function () {
            Route::get('/institution', [TenantInstitutionController::class, 'edit'])->name('institution.edit');
        });
        Route::middleware('permission:settings.institution.update')->group(function () {
            Route::put('/institution', [TenantInstitutionController::class, 'update'])->name('institution.update');
        });

        Route::middleware('permission:settings.domains.view')->group(function () {
            Route::get('/domains', [TenantDomainController::class, 'index'])->name('domains.index');
        });
        Route::middleware('permission:settings.domains.update')->group(function () {
            Route::post('/domains', [TenantDomainController::class, 'store'])->name('domains.store');
            Route::post('/domains/{domain}/stub-verify', [TenantDomainController::class, 'stubVerify'])->name('domains.stub-verify');
        });

        Route::middleware('permission:settings.subscription.view')->group(function () {
            Route::get('/subscription', [TenantSubscriptionController::class, 'show'])->name('subscription.show');
        });
        Route::middleware('permission:settings.modules.view')->group(function () {
            Route::get('/modules', [TenantModuleController::class, 'index'])->name('modules.index');
        });
        Route::middleware('permission:settings.usage.view')->group(function () {
            Route::get('/usage', [TenantUsageController::class, 'index'])->name('usage.index');
        });
        Route::middleware('permission:settings.usage.update')->group(function () {
            Route::put('/usage/stub', [TenantUsageController::class, 'updateStub'])->name('usage.stub');
        });

        // Identity / RBAC
        Route::middleware('permission:settings.users.view')->group(function () {
            Route::get('/users', [TenantUserController::class, 'index'])->name('users.index');
        });
        Route::middleware('permission:settings.users.create')->group(function () {
            Route::get('/users/create', [TenantUserController::class, 'create'])->name('users.create');
            Route::post('/users', [TenantUserController::class, 'store'])->name('users.store');
        });
        Route::middleware('permission:settings.users.update')->group(function () {
            Route::get('/users/{user}/edit', [TenantUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [TenantUserController::class, 'update'])->name('users.update');
        });
        Route::middleware('permission:settings.users.delete')->group(function () {
            Route::delete('/users/{user}', [TenantUserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:settings.roles.view')->group(function () {
            Route::get('/roles', [TenantRoleController::class, 'index'])->name('roles.index');
        });
        Route::middleware('permission:settings.roles.create')->group(function () {
            Route::get('/roles/create', [TenantRoleController::class, 'create'])->name('roles.create');
            Route::post('/roles', [TenantRoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:settings.roles.update')->group(function () {
            Route::get('/roles/{role}/edit', [TenantRoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [TenantRoleController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:settings.roles.delete')->group(function () {
            Route::delete('/roles/{role}', [TenantRoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::get('/permissions', [TenantPermissionController::class, 'index'])->name('permissions.index');

        Route::middleware('permission:settings.memberships.view')->group(function () {
            Route::get('/memberships', [TenantMembershipController::class, 'index'])->name('memberships.index');
        });
        Route::middleware('permission:settings.memberships.update')->group(function () {
            Route::patch('/memberships/{membership}', [TenantMembershipController::class, 'update'])->name('memberships.update');
        });
        Route::middleware('permission:settings.memberships.delete')->group(function () {
            Route::delete('/memberships/{membership}', [TenantMembershipController::class, 'destroy'])->name('memberships.destroy');
        });

        Route::middleware('permission:settings.audit.view')->group(function () {
            Route::get('/audit', [TenantAuditLogController::class, 'index'])->name('audit.index');
        });
        Route::middleware('permission:settings.login_history.view')->group(function () {
            Route::get('/login-history', [TenantLoginHistoryController::class, 'index'])->name('login-history.index');
        });
        Route::middleware('permission:settings.sessions.view')->group(function () {
            Route::get('/sessions', [TenantSessionController::class, 'index'])->name('sessions.index');
        });
        Route::middleware('permission:settings.sessions.revoke')->group(function () {
            Route::delete('/sessions/others', [TenantSessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
            Route::delete('/sessions/{session}', [TenantSessionController::class, 'destroy'])->name('sessions.destroy');
        });
    });

// Back-compat alias used by Phase 01 tests / bookmarks
Route::redirect('/dashboard', '/app')->name('dashboard');

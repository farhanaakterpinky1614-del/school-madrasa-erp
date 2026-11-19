<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterTenantController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\InstitutionController as PlatformInstitutionController;
use App\Http\Controllers\Platform\ModuleController as PlatformModuleController;
use App\Http\Controllers\Platform\PlanController as PlatformPlanController;
use App\Http\Controllers\Platform\TenantController as PlatformTenantController;
use App\Http\Controllers\Platform\UsageController as PlatformUsageController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\DomainController as TenantDomainController;
use App\Http\Controllers\Tenant\InstitutionController as TenantInstitutionController;
use App\Http\Controllers\Tenant\ModuleController as TenantModuleController;
use App\Http\Controllers\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\Tenant\UsageController as TenantUsageController;
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
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterTenantController::class, 'create'])->name('register');
    Route::post('/register', [RegisterTenantController::class, 'store'])->name('register.store');
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
    });

/*
|--------------------------------------------------------------------------
| Tenant institution admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant.admin', 'tenant.context'])
    ->prefix('app')
    ->name('tenant.')
    ->group(function () {
        Route::get('/', TenantDashboardController::class)->name('dashboard');
        Route::get('/institution', [TenantInstitutionController::class, 'edit'])->name('institution.edit');
        Route::put('/institution', [TenantInstitutionController::class, 'update'])->name('institution.update');

        Route::get('/domains', [TenantDomainController::class, 'index'])->name('domains.index');
        Route::post('/domains', [TenantDomainController::class, 'store'])->name('domains.store');
        Route::post('/domains/{domain}/stub-verify', [TenantDomainController::class, 'stubVerify'])->name('domains.stub-verify');

        Route::get('/subscription', [TenantSubscriptionController::class, 'show'])->name('subscription.show');
        Route::get('/modules', [TenantModuleController::class, 'index'])->name('modules.index');
        Route::get('/usage', [TenantUsageController::class, 'index'])->name('usage.index');
        Route::put('/usage/stub', [TenantUsageController::class, 'updateStub'])->name('usage.stub');
    });

// Back-compat alias used by Phase 01 tests / bookmarks
Route::redirect('/dashboard', '/app')->name('dashboard');

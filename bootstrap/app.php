<?php

use App\Core\Http\Middleware\SetLocale;
use App\Http\Middleware\EnsureApiTenant;
use App\Http\Middleware\EnsureFeature;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureTenantAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Identity\Http\Middleware\EnsurePermission;
use Modules\Identity\Http\Middleware\EnsureTenantMember;
use Modules\Identity\Http\Middleware\SyncPermissionsTeam;
use Modules\Tenancy\Http\Middleware\EnsureTenantContext;
use Modules\Tenancy\Http\Middleware\ResolveTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            ResolveTenant::class,
            SyncPermissionsTeam::class,
            SecurityHeaders::class,
        ]);

        $middleware->api(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'platform.admin' => EnsurePlatformAdmin::class,
            'tenant.admin' => EnsureTenantAdmin::class,
            'tenant.member' => EnsureTenantMember::class,
            'tenant.context' => EnsureTenantContext::class,
            'permission' => EnsurePermission::class,
            'api.tenant' => EnsureApiTenant::class,
            'feature' => EnsureFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

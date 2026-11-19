<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Modules\Tenancy\Services\TenantResolver;
use Tests\Support\Tenancy;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class, Tenancy::class);

it('resolves active tenants from subdomain hosts', function () {
    $this->registerTenant(['slug' => 'resolve-me']);

    $request = Request::create('http://resolve-me.'.config('sme.tenancy.base_domain').'/app', 'GET');
    $tenant = app(TenantResolver::class)->resolveFromRequest($request);

    expect($tenant)->not->toBeNull()
        ->and($tenant->slug)->toBe('resolve-me');
});

it('does not resolve from central hosts', function () {
    $this->registerTenant(['slug' => 'central-skip']);

    $request = Request::create('http://localhost/app', 'GET');
    $tenant = app(TenantResolver::class)->resolveFromRequest($request);

    expect($tenant)->toBeNull();
});

it('treats admin host as platform', function () {
    $resolver = app(TenantResolver::class);

    expect($resolver->isPlatformHost('admin.localhost'))->toBeTrue()
        ->and($resolver->isReservedSlug('www'))->toBeTrue();
});

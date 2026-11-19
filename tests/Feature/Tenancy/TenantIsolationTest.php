<?php

use App\Core\Tenancy\CurrentTenant;
use Modules\Tenancy\Models\Institution;
use Modules\Tenancy\Models\TenantNote;

it('scopes institution queries to the current tenant only', function () {
    $a = $this->registerTenant(['slug' => 'alpha-school', 'institution_name' => 'Alpha School']);
    $b = $this->registerTenant(['slug' => 'beta-school', 'institution_name' => 'Beta School']);

    $this->bindTenant($a['tenant']);

    $visible = Institution::query()->pluck('name')->all();

    expect($visible)->toContain('Alpha School')
        ->and($visible)->not->toContain('Beta School');

    expect(Institution::query()->find($b['institution']->id))->toBeNull();
});

it('forces tenant_id from context and ignores forged client values', function () {
    $a = $this->registerTenant(['slug' => 'forge-a']);
    $b = $this->registerTenant(['slug' => 'forge-b']);

    $this->bindTenant($a['tenant']);

    $created = TenantNote::query()->create([
        'tenant_id' => $b['tenant']->id, // forged — must be overwritten
        'title' => 'Should Belong To A',
        'body' => 'isolation check',
    ]);

    expect($created->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(TenantNote::query()->where('title', 'Should Belong To A')->exists())->toBeFalse();
});

it('blocks cross-tenant access via host resolution', function () {
    $a = $this->registerTenant(['slug' => 'host-a', 'admin_email' => 'admin-a@example.test']);
    $this->registerTenant(['slug' => 'host-b', 'admin_email' => 'admin-b@example.test']);

    $host = 'host-b.'.config('sme.tenancy.base_domain');

    $this->actingAs($a['user'])
        ->get('http://'.$host.'/app')
        ->assertForbidden();
});

it('allows tenant admin on matching subdomain host', function () {
    $a = $this->registerTenant(['slug' => 'green-academy', 'admin_email' => 'green@example.test', 'institution_name' => 'Green Academy']);

    $host = 'green-academy.'.config('sme.tenancy.base_domain');

    $this->actingAs($a['user'])
        ->get('http://'.$host.'/app')
        ->assertOk()
        ->assertSee('Green Academy', false);
});


it('clears tenant context between requests via middleware', function () {
    $a = $this->registerTenant(['slug' => 'clear-a']);
    app(CurrentTenant::class)->set($a['tenant']);
    expect(app(CurrentTenant::class)->id())->toBe($a['tenant']->id);

    $this->get(route('login'))->assertOk();

    expect(app(CurrentTenant::class)->check())->toBeFalse();
});

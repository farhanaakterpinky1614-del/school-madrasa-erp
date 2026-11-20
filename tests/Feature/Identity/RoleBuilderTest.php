<?php

use Modules\Identity\Models\AuditLog;

it('creates a custom role from the permission catalog and audits it', function () {
    $registered = $this->registerTenant([
        'slug' => 'builder',
        'admin_email' => 'builder@example.test',
    ]);

    $this->actingAs($registered['user'])
        ->post(route('tenant.roles.store'), [
            'name' => 'Branch Coordinator',
            'permissions' => [
                'settings.users.view',
                'branches.branch.view',
                'platform.tenants.view', // must be stripped
            ],
        ])
        ->assertRedirect(route('tenant.roles.index'));

    setPermissionsTeamId($registered['tenant']->id);

    $role = \Spatie\Permission\Models\Role::query()
        ->where('tenant_id', $registered['tenant']->id)
        ->where('name', 'Branch Coordinator')
        ->first();

    expect($role)->not->toBeNull();
    expect($role->permissions->pluck('name')->all())
        ->toContain('settings.users.view')
        ->toContain('branches.branch.view')
        ->not->toContain('platform.tenants.view');

    expect(AuditLog::queryWithoutTenantScope()
        ->where('action', 'roles.created')
        ->where('tenant_id', $registered['tenant']->id)
        ->exists())->toBeTrue();
});

it('refuses deleting Institution Admin role', function () {
    $registered = $this->registerTenant([
        'slug' => 'no-delete-admin',
        'admin_email' => 'nodelete@example.test',
    ]);

    setPermissionsTeamId($registered['tenant']->id);
    $role = \Spatie\Permission\Models\Role::findByName('Institution Admin', 'web');

    $this->actingAs($registered['user'])
        ->delete(route('tenant.roles.destroy', $role))
        ->assertForbidden();
});

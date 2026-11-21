<?php

use Modules\Academics\Models\HomeworkItem;
use Modules\Academics\Models\Room;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;

it('scopes rooms and homework to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'acad-a', 'admin_email' => 'acad-a@test.test']);
    $b = $this->registerTenant(['slug' => 'acad-b', 'admin_email' => 'acad-b@test.test']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    app(DemoHrProvisioner::class)->provision($a['tenant']);
    app(DemoAcademicProvisioner::class)->provision($a['tenant']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoHrProvisioner::class)->provision($b['tenant']);
    app(DemoAcademicProvisioner::class)->provision($b['tenant']);

    $this->bindTenant($a['tenant']);
    expect(Room::query()->count())->toBeGreaterThan(0);
    expect(HomeworkItem::query()->count())->toBeGreaterThan(0);

    $foreignRoom = Room::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();
    expect(Room::query()->find($foreignRoom->id))->toBeNull();

    $foreignHw = HomeworkItem::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();
    expect(HomeworkItem::query()->find($foreignHw->id))->toBeNull();
});

it('forces tenant_id when creating rooms and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-acad-a', 'admin_email' => 'faa@test.test']);
    $b = $this->registerTenant(['slug' => 'forge-acad-b', 'admin_email' => 'fab@test.test']);

    $this->bindTenant($a['tenant']);

    $room = Room::query()->create([
        'tenant_id' => $b['tenant']->id,
        'name' => 'Forged Room',
        'code' => 'FX1',
        'room_type' => 'classroom',
        'is_active' => true,
    ]);

    expect($room->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Room::query()->where('code', 'FX1')->exists())->toBeFalse();
});

it('blocks cross-tenant syllabus show by id', function () {
    $a = $this->registerTenant(['slug' => 'xacad-a', 'admin_email' => 'xaa@test.test']);
    $b = $this->registerTenant(['slug' => 'xacad-b', 'admin_email' => 'xab@test.test']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoHrProvisioner::class)->provision($b['tenant']);
    app(DemoAcademicProvisioner::class)->provision($b['tenant']);

    $foreign = \Modules\Academics\Models\Syllabus::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    $this->actingAs($a['user'])
        ->get(route('tenant.syllabi.show', $foreign))
        ->assertNotFound();
});

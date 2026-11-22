<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Communications\Models\Announcement;
use Modules\Communications\Models\NotificationLog;
use Modules\Communications\Models\NotificationTemplate;
use Modules\Communications\Services\DemoCommunicationsProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes announcements and templates to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'comm-a', 'admin_email' => 'comm-a@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'comm-b', 'admin_email' => 'comm-b@test.test', 'plan_code' => 'professional']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoCommunicationsProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(Announcement::query()->count())->toBeGreaterThan(0);
    expect(NotificationTemplate::query()->count())->toBeGreaterThan(0);
    expect(NotificationLog::query()->count())->toBeGreaterThan(0);

    $foreignAnnouncement = Announcement::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignTemplate = NotificationTemplate::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(Announcement::query()->find($foreignAnnouncement->id))->toBeNull();
    expect(NotificationTemplate::query()->find($foreignTemplate->id))->toBeNull();
});

it('forces tenant_id when creating an announcement and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-comm-a', 'admin_email' => 'fca@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'forge-comm-b', 'admin_email' => 'fcb@test.test', 'plan_code' => 'professional']);

    $this->bindTenant($a['tenant']);

    $row = Announcement::query()->create([
        'tenant_id' => $b['tenant']->id,
        'title' => 'Forged notice',
        'body' => 'Should stay on tenant A',
        'audience_type' => Announcement::AUDIENCE_ALL,
        'status' => Announcement::STATUS_DRAFT,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(Announcement::query()->where('title', 'Forged notice')->exists())->toBeFalse();
});

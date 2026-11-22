<?php

use App\Exceptions\QuotaExceededException;
use Modules\Platform\Models\TenantUsageMetric;
use Modules\Platform\Services\UsageTracker;

it('exposes remaining and exceeded in usage summary', function () {
    $registered = $this->registerTenant(['slug' => 'quota-sum', 'plan_code' => 'starter']);
    $usage = app(UsageTracker::class);
    $usage->set($registered['tenant'], TenantUsageMetric::STUDENTS, 150);

    $summary = $usage->summary($registered['tenant']);

    expect($summary['students']['limit'])->toBe(200)
        ->and($summary['students']['remaining'])->toBe(50)
        ->and($summary['students']['exceeded'])->toBeFalse();
});

it('hard-blocks consume beyond plan limit', function () {
    $registered = $this->registerTenant(['slug' => 'quota-hard', 'plan_code' => 'starter']);
    $usage = app(UsageTracker::class);
    $usage->set($registered['tenant'], TenantUsageMetric::STUDENTS, 200);

    expect(fn () => $usage->consume($registered['tenant'], TenantUsageMetric::STUDENTS))
        ->toThrow(QuotaExceededException::class);
});

it('allows unlimited students on enterprise', function () {
    $registered = $this->registerTenant([
        'slug' => 'quota-ent',
        'plan_code' => 'enterprise',
        'admin_email' => 'quotaent@test.test',
    ]);
    $usage = app(UsageTracker::class);
    $usage->set($registered['tenant'], TenantUsageMetric::STUDENTS, 50000);

    expect($usage->wouldExceed($registered['tenant'], TenantUsageMetric::STUDENTS))->toBeFalse();
    $usage->consume($registered['tenant'], TenantUsageMetric::STUDENTS);
    expect($usage->get($registered['tenant'], TenantUsageMetric::STUDENTS))->toBe(50001);
});

it('rejects usage stub update over plan limit', function () {
    $registered = $this->registerTenant(['slug' => 'quota-ui', 'plan_code' => 'starter']);

    $this->actingAs($registered['user'])
        ->withTenantHost('quota-ui')
        ->put(route('tenant.usage.stub'), [
            'students' => 9999,
            'staff' => 1,
            'storage_mb' => 10,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('quota');
});

<?php

use Modules\Platform\Models\TenantUsageMetric;
use Modules\Platform\Services\UsageTracker;
use Modules\Students\Models\Student;

it('rejects unauthenticated api requests', function () {
    $this->getJson('/api/v1/students')->assertUnauthorized();
});

it('rejects starter plan tokens without api_access', function () {
    $registered = $this->registerTenant(['slug' => 'api-starter', 'plan_code' => 'starter']);
    $token = $registered['user']->createToken('t')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/students')
        ->assertForbidden();
});

it('lists students for professional plan with sanctum token and scopes tenant', function () {
    $a = $this->registerTenant(['slug' => 'api-a', 'plan_code' => 'professional', 'admin_email' => 'apia@test.test']);
    $b = $this->registerTenant(['slug' => 'api-b', 'plan_code' => 'professional', 'admin_email' => 'apib@test.test']);

    $this->bindTenant($a['tenant']);
    Student::query()->create([
        'first_name' => 'Alice',
        'last_name' => 'A',
        'student_code' => 'A-1',
        'status' => Student::STATUS_ACTIVE,
    ]);

    $this->bindTenant($b['tenant']);
    Student::query()->create([
        'first_name' => 'Bob',
        'last_name' => 'B',
        'student_code' => 'B-1',
        'status' => Student::STATUS_ACTIVE,
    ]);

    $token = $a['user']->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/students');
    $response->assertOk();
    $names = collect($response->json('data'))->pluck('first_name');
    expect($names)->toContain('Alice')->not->toContain('Bob');
});

it('enforces student quota on api create', function () {
    $registered = $this->registerTenant(['slug' => 'api-quota', 'plan_code' => 'starter', 'admin_email' => 'quota@test.test']);
    // Force api_access on starter plan feature for this test via professional-like override:
    // Use professional but drive quota to 0 remaining by setting students to max.
    $pro = $this->registerTenant(['slug' => 'api-quota-pro', 'plan_code' => 'professional', 'admin_email' => 'quotapro@test.test']);
    $usage = app(UsageTracker::class);
    $usage->set($pro['tenant'], TenantUsageMetric::STUDENTS, 2000);

    $token = $pro['user']->createToken('t')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/students', [
            'first_name' => 'Over',
            'last_name' => 'Quota',
        ])
        ->assertStatus(422)
        ->assertJsonPath('metric', 'students');
});

it('returns fees summary for authorized token', function () {
    $registered = $this->registerTenant(['slug' => 'api-fees', 'plan_code' => 'professional', 'admin_email' => 'apifees@test.test']);
    $token = $registered['user']->createToken('t')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/fees/summary')
        ->assertOk()
        ->assertJsonPath('currency', 'BDT');
});

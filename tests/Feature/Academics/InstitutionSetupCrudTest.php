<?php

use Modules\Academics\Models\Branch;
use Modules\Academics\Models\GradingScale;
use Modules\Academics\Services\DemoStructureProvisioner;

it('creates a branch for the authenticated tenant', function () {
    $registered = $this->registerTenant([
        'slug' => 'crud-branch',
        'admin_email' => 'crud-branch@setup.test',
    ]);

    $this->actingAs($registered['user'])
        ->post(route('tenant.branches.store'), [
            'name' => 'North Campus',
            'code' => 'NORTH',
            'city' => 'Gazipur',
            'is_active' => 1,
        ])
        ->assertRedirect(route('tenant.branches.index'));

    $this->bindTenant($registered['tenant']);
    expect(Branch::query()->where('code', 'NORTH')->exists())->toBeTrue();
});

it('updates institution profile type and extended fields', function () {
    $registered = $this->registerTenant([
        'slug' => 'crud-inst',
        'admin_email' => 'crud-inst@setup.test',
        'institution_type' => 'school',
    ]);

    $this->actingAs($registered['user'])
        ->put(route('tenant.institution.update'), [
            'name' => 'Updated Demo College',
            'institution_type' => 'college',
            'eiin' => '998877',
            'board' => 'Dhaka',
            'email' => 'office@demo.test',
            'city' => 'Dhaka',
        ])
        ->assertRedirect();

    $registered['institution']->refresh();
    expect($registered['institution']->institution_type)->toBe('college')
        ->and($registered['institution']->eiin)->toBe('998877')
        ->and($registered['institution']->name)->toBe('Updated Demo College');
});

it('seeds demo grading scale bands for a provisioned tenant', function () {
    $registered = $this->registerTenant([
        'slug' => 'crud-grade',
        'admin_email' => 'crud-grade@setup.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);

    $this->bindTenant($registered['tenant']);

    $scale = GradingScale::query()->where('code', 'BD-GPA')->first();
    expect($scale)->not->toBeNull()
        ->and($scale->bands()->count())->toBe(7);

    $this->actingAs($registered['user'])
        ->get(route('tenant.grading.edit', $scale))
        ->assertOk()
        ->assertSee('A+', false);
});

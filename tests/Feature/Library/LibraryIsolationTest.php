<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Library\Models\LibraryBook;
use Modules\Library\Models\LibraryIssue;
use Modules\Library\Services\DemoLibraryProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('scopes library books and issues to the current tenant', function () {
    $a = $this->registerTenant(['slug' => 'lib-a', 'admin_email' => 'lib-a@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'lib-b', 'admin_email' => 'lib-b@test.test', 'plan_code' => 'professional']);

    foreach ([$a, $b] as $registered) {
        app(DemoStructureProvisioner::class)->provision($registered['tenant']);
        app(DemoStudentProvisioner::class)->provision($registered['tenant']);
        app(DemoHrProvisioner::class)->provision($registered['tenant']);
        app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
        app(DemoLibraryProvisioner::class)->provision($registered['tenant']);
    }

    $this->bindTenant($a['tenant']);
    expect(LibraryBook::query()->count())->toBeGreaterThan(0);
    expect(LibraryIssue::query()->count())->toBeGreaterThan(0);

    $foreignBook = LibraryBook::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();
    $foreignIssue = LibraryIssue::queryWithoutTenantScope()
        ->where('tenant_id', $b['tenant']->id)
        ->firstOrFail();

    expect(LibraryBook::query()->find($foreignBook->id))->toBeNull();
    expect(LibraryIssue::query()->find($foreignIssue->id))->toBeNull();
});

it('forces tenant_id when creating a library book and ignores forged values', function () {
    $a = $this->registerTenant(['slug' => 'forge-lib-a', 'admin_email' => 'fla@test.test', 'plan_code' => 'professional']);
    $b = $this->registerTenant(['slug' => 'forge-lib-b', 'admin_email' => 'flb@test.test', 'plan_code' => 'professional']);

    app(DemoStructureProvisioner::class)->provision($a['tenant']);
    $this->bindTenant($a['tenant']);

    $row = LibraryBook::query()->create([
        'tenant_id' => $b['tenant']->id,
        'title' => 'Forged Title',
        'author' => 'Nobody',
        'isbn' => 'FORGE-ISBN',
        'language' => LibraryBook::LANG_EN,
        'is_active' => true,
    ]);

    expect($row->tenant_id)->toBe($a['tenant']->id);

    $this->bindTenant($b['tenant']);
    expect(LibraryBook::query()->where('isbn', 'FORGE-ISBN')->exists())->toBeFalse();
});

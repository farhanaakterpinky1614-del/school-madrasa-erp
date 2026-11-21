<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Students\Models\Student;
use Modules\Students\Services\DemoStudentProvisioner;

it('uploads a private tenant-scoped student photo and serves it on the id card', function () {
    Storage::fake('local');

    $registered = $this->registerTenant([
        'slug' => 'photo-stu',
        'admin_email' => 'photo-stu@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    $this->bindTenant($registered['tenant']);

    $student = Student::query()->where('first_name', 'Ayesha')->firstOrFail();

    $file = UploadedFile::fake()->image('ayesha.jpg', 240, 320);

    $this->actingAs($registered['user'])
        ->post(route('tenant.students.photo.store', $student), [
            'photo' => $file,
            'redirect_to' => 'id-card',
        ])
        ->assertRedirect(route('tenant.students.id-card', $student));

    $student->refresh();
    expect($student->photo_path)->not->toBeNull()
        ->and($student->photo_path)->toContain('tenants/'.$student->tenant_id.'/students/'.$student->id.'/photo');

    Storage::disk('local')->assertExists($student->photo_path);

    $this->actingAs($registered['user'])
        ->get(route('tenant.students.photo.show', $student))
        ->assertOk();

    $this->actingAs($registered['user'])
        ->get(route('tenant.students.id-card', $student))
        ->assertOk()
        ->assertSee($student->full_name, false)
        ->assertSee(route('tenant.students.photo.show', $student), false)
        ->assertSee(__('ui.change_photo'), false)
        ->assertSee(__('ui.id_card_signature'), false)
        ->assertSee('sme-id-card', false)
        ->assertDontSee(__('ui.photo_placeholder'), false);
});

it('shows a photo placeholder on the id card when no photo is set', function () {
    $registered = $this->registerTenant([
        'slug' => 'nophoto-stu',
        'admin_email' => 'nophoto-stu@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);

    $student = Student::queryWithoutTenantScope()
        ->where('tenant_id', $registered['tenant']->id)
        ->where('first_name', 'Rahim')
        ->firstOrFail();

    $this->actingAs($registered['user'])
        ->get(route('tenant.students.id-card', $student))
        ->assertOk()
        ->assertSee($student->full_name, false)
        ->assertSee(__('ui.photo_placeholder'), false)
        ->assertSee(__('ui.add_photo'), false);
});

it('blocks cross-tenant access to another tenant student photo', function () {
    Storage::fake('local');

    $a = $this->registerTenant(['slug' => 'ph-a', 'admin_email' => 'pha@test.test']);
    $b = $this->registerTenant(['slug' => 'ph-b', 'admin_email' => 'phb@test.test']);

    app(DemoStructureProvisioner::class)->provision($b['tenant']);
    app(DemoStudentProvisioner::class)->provision($b['tenant']);

    $foreign = Student::queryWithoutTenantScope()->where('tenant_id', $b['tenant']->id)->firstOrFail();
    $path = 'tenants/'.$foreign->tenant_id.'/students/'.$foreign->id.'/photo/face.jpg';
    Storage::disk('local')->put($path, 'fake-image');
    $foreign->update(['photo_path' => $path]);

    $this->actingAs($a['user'])
        ->get(route('tenant.students.photo.show', $foreign))
        ->assertNotFound();
});

<?php

use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Communications\Models\Announcement;
use Modules\Communications\Models\NotificationLog;
use Modules\Communications\Models\NotificationTemplate;
use Modules\Communications\Services\DemoCommunicationsProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;
use Modules\Students\Services\DemoStudentProvisioner;

it('publishes and unpublishes an announcement via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-comm-announce',
        'admin_email' => 'wf-comm-announce@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoCommunicationsProvisioner::class)->provision($registered['tenant']);

    $this->actingAs($registered['user'])
        ->post(route('tenant.communications.announcements.store'), [
            'title' => 'Exam schedule update',
            'body' => 'Midterm exams start next week.',
            'audience_type' => Announcement::AUDIENCE_ALL,
        ])
        ->assertRedirect();

    $announcement = Announcement::query()
        ->where('title', 'Exam schedule update')
        ->firstOrFail();

    expect($announcement->status)->toBe(Announcement::STATUS_DRAFT);

    $this->actingAs($registered['user'])
        ->post(route('tenant.communications.announcements.publish', $announcement))
        ->assertRedirect();

    $announcement->refresh();
    expect($announcement->status)->toBe(Announcement::STATUS_PUBLISHED);
    expect($announcement->published_at)->not->toBeNull();

    $this->actingAs($registered['user'])
        ->get(route('tenant.communications.inbox.index'))
        ->assertOk()
        ->assertSee('Exam schedule update');

    $this->actingAs($registered['user'])
        ->post(route('tenant.communications.announcements.unpublish', $announcement))
        ->assertRedirect();

    $announcement->refresh();
    expect($announcement->status)->toBe(Announcement::STATUS_UNPUBLISHED);
});

it('sends from a template and writes a stubbed delivery log', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-comm-send',
        'admin_email' => 'wf-comm-send@test.test',
        'plan_code' => 'professional',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoStudentProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);
    app(DemoCommunicationsProvisioner::class)->provision($registered['tenant']);

    $template = NotificationTemplate::query()
        ->where('code', 'FEE_DUE_SMS')
        ->firstOrFail();

    $before = NotificationLog::query()->count();

    $this->actingAs($registered['user'])
        ->post(route('tenant.communications.send.store'), [
            'notification_template_id' => $template->id,
            'recipient' => '01799887766',
            'student_name' => 'Karim Ali',
            'amount' => '2500',
        ])
        ->assertRedirect(route('tenant.communications.logs.index'));

    expect(NotificationLog::query()->count())->toBe($before + 1);

    $log = NotificationLog::query()->orderByDesc('id')->firstOrFail();
    expect($log->channel)->toBe(NotificationLog::CHANNEL_SMS);
    expect($log->recipient)->toBe('01799887766');
    expect($log->status)->toBe(NotificationLog::STATUS_STUBBED);
    expect($log->driver)->toBe('stub');
    expect($log->body)->toContain('Karim Ali');
    expect($log->body)->toContain('2500');
    expect($log->notification_template_id)->toBe($template->id);
});

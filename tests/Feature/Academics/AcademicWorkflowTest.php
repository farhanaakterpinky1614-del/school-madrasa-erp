<?php

use Modules\Academics\Models\ClassRoutine;
use Modules\Academics\Models\HomeworkItem;
use Modules\Academics\Models\LessonPlan;
use Modules\Academics\Models\Period;
use Modules\Academics\Models\Room;
use Modules\Academics\Models\SchoolClass;
use Modules\Academics\Models\Subject;
use Modules\Academics\Models\Syllabus;
use Modules\Academics\Models\SyllabusTopic;
use Modules\Academics\Services\DemoAcademicProvisioner;
use Modules\Academics\Services\DemoStructureProvisioner;
use Modules\Hr\Services\DemoHrProvisioner;

it('creates syllabus topics and publishes homework via HTTP', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-acad',
        'admin_email' => 'wf-acad@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $syllabus = Syllabus::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.syllabi.topics.store', $syllabus), [
            'title' => 'Unit 3 — Review',
            'sort_order' => 3,
            'estimated_periods' => 4,
            'description' => 'Revision unit',
        ])
        ->assertRedirect(route('tenant.syllabi.show', $syllabus));

    expect(SyllabusTopic::query()->where('title', 'Unit 3 — Review')->exists())->toBeTrue();

    $class = SchoolClass::query()->firstOrFail();
    $subject = Subject::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.homework.store'), [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Extra practice set',
            'status' => HomeworkItem::STATUS_PUBLISHED,
            'due_on' => now()->addDays(2)->toDateString(),
        ])
        ->assertRedirect(route('tenant.homework.index'));

    expect(HomeworkItem::query()->where('title', 'Extra practice set')->exists())->toBeTrue();
});

it('stores a class routine slot with room and period', function () {
    $registered = $this->registerTenant([
        'slug' => 'wf-routine',
        'admin_email' => 'wf-routine@test.test',
    ]);
    app(DemoStructureProvisioner::class)->provision($registered['tenant']);
    app(DemoHrProvisioner::class)->provision($registered['tenant']);
    app(DemoAcademicProvisioner::class)->provision($registered['tenant']);

    $class = SchoolClass::query()->firstOrFail();
    $period = Period::query()->where('is_break', false)->orderBy('sort_order')->firstOrFail();
    $subject = Subject::query()->firstOrFail();
    $room = Room::query()->firstOrFail();

    $this->actingAs($registered['user'])
        ->post(route('tenant.routines.store'), [
            'school_class_id' => $class->id,
            'day_of_week' => 5,
            'period_id' => $period->id,
            'subject_id' => $subject->id,
            'room_id' => $room->id,
        ])
        ->assertRedirect(route('tenant.routines.index'));

    expect(ClassRoutine::query()
        ->where('day_of_week', 5)
        ->where('period_id', $period->id)
        ->exists())->toBeTrue();

    expect(LessonPlan::query()->where('status', LessonPlan::STATUS_PUBLISHED)->count())->toBeGreaterThan(0);
});

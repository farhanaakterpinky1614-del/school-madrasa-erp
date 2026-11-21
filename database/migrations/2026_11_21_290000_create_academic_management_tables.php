<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->string('room_type', 32)->default('classroom'); // classroom|lab|hall|library|other
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('building')->nullable();
            $table->string('floor', 32)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'room_type']);
        });

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->boolean('is_break')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'sort_order']);
        });

        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun … 6=Sat (BD week often Sat–Thu)
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'academic_year_id', 'school_class_id', 'section_id', 'day_of_week', 'period_id'],
                'class_routines_slot_unique'
            );
            $table->index(['tenant_id', 'staff_id', 'day_of_week']);
            $table->index(['tenant_id', 'school_class_id', 'section_id']);
        });

        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curriculum_id')->nullable()->constrained('curricula')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'school_class_id', 'subject_id']);
            $table->unique(
                ['tenant_id', 'academic_year_id', 'school_class_id', 'subject_id'],
                'syllabi_class_subject_unique'
            );
        });

        Schema::create('syllabus_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('syllabus_id')->constrained('syllabi')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('syllabus_topics')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('estimated_periods')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'syllabus_id', 'sort_order']);
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('syllabus_topic_id')->nullable()->constrained('syllabus_topics')->nullOnDelete();
            $table->string('title');
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->text('resources')->nullable();
            $table->text('homework_notes')->nullable();
            $table->date('planned_on')->nullable();
            $table->string('status', 32)->default('draft'); // draft|published
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'planned_on']);
            $table->index(['tenant_id', 'school_class_id', 'subject_id']);
            $table->index(['tenant_id', 'staff_id']);
        });

        Schema::create('homework_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('assigned_on')->nullable();
            $table->date('due_on')->nullable();
            $table->string('status', 32)->default('draft'); // draft|published|closed
            $table->timestamps();

            $table->index(['tenant_id', 'due_on']);
            $table->index(['tenant_id', 'school_class_id', 'subject_id']);
        });

        // Student classwork / academic assignments (distinct from HR teacher_assignments)
        Schema::create('classwork_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('assigned_on')->nullable();
            $table->date('due_on')->nullable();
            $table->decimal('max_marks', 8, 2)->nullable();
            $table->string('status', 32)->default('draft'); // draft|published|closed
            $table->timestamps();

            $table->index(['tenant_id', 'due_on']);
            $table->index(['tenant_id', 'school_class_id', 'subject_id']);
        });

        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('material_type', 32)->default('note'); // note|file|link
            $table->string('file_path')->nullable();
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'school_class_id', 'subject_id']);
            $table->index(['tenant_id', 'material_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
        Schema::dropIfExists('classwork_assignments');
        Schema::dropIfExists('homework_items');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('syllabus_topics');
        Schema::dropIfExists('syllabi');
        Schema::dropIfExists('class_routines');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('rooms');
    }
};

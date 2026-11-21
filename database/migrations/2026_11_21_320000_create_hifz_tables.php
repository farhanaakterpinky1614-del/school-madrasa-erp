<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hifz_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status', 32)->default('active'); // active|completed|paused
            $table->unsignedTinyInteger('current_juz')->default(1); // 1–30 (para)
            $table->unsignedSmallInteger('current_ayah')->default(1);
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('started_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id'], 'hifz_profiles_student_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_teacher_id'], 'hifz_profiles_teacher_idx');
            $table->index(['tenant_id', 'current_juz'], 'hifz_profiles_juz_idx');
        });

        Schema::create('hifz_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hifz_profile_id')->constrained('hifz_profiles')->cascadeOnDelete();
            $table->date('logged_on');
            $table->unsignedTinyInteger('from_juz');
            $table->unsignedSmallInteger('from_ayah')->default(1);
            $table->unsignedTinyInteger('to_juz');
            $table->unsignedSmallInteger('to_ayah')->default(1);
            $table->string('quality', 32)->nullable(); // excellent|good|average|needs_work
            $table->text('teacher_notes')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'hifz_profile_id', 'logged_on'], 'hifz_progress_profile_date_idx');
        });

        Schema::create('hifz_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hifz_profile_id')->constrained('hifz_profiles')->cascadeOnDelete();
            $table->string('label')->nullable(); // e.g. Dawr cycle name
            $table->unsignedTinyInteger('from_juz');
            $table->unsignedTinyInteger('to_juz');
            $table->date('scheduled_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->string('status', 32)->default('scheduled'); // scheduled|in_progress|completed|missed
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'hifz_profile_id', 'status'], 'hifz_revisions_profile_status_idx');
            $table->index(['tenant_id', 'scheduled_on'], 'hifz_revisions_scheduled_idx');
        });

        Schema::create('islamic_subjects_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('kitab_name')->nullable();
            $table->string('department_label')->nullable();
            $table->string('qawmi_level', 64)->nullable();
            $table->json('meta')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'subject_id'], 'islamic_subjects_meta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('islamic_subjects_meta');
        Schema::dropIfExists('hifz_revisions');
        Schema::dropIfExists('hifz_progress_logs');
        Schema::dropIfExists('hifz_profiles');
    }
};

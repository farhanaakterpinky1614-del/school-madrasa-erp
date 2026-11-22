<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->foreignId('grading_scale_id')->constrained('grading_scales')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('grading_scheme_id')->nullable()->constrained('grading_schemes')->nullOnDelete();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status', 32)->default('draft'); // draft|scheduled|ongoing|closed
            $table->string('board_name')->nullable();
            $table->string('board_roll_prefix', 64)->nullable();
            $table->json('board_meta')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'school_class_id', 'academic_year_id'], 'exams_class_year_idx');
        });

        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->decimal('full_marks', 8, 2)->default(100);
            $table->decimal('pass_marks', 8, 2)->default(33);
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'exam_id', 'subject_id'], 'exam_subjects_unique');
            $table->index(['tenant_id', 'exam_id']);
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained('exam_subjects')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->string('grade', 16)->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->text('remarks')->nullable();
            $table->string('entry_status', 16)->default('draft'); // draft|submitted
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'exam_subject_id', 'student_id'], 'marks_exam_subject_student_unique');
            $table->index(['tenant_id', 'exam_id', 'section_id'], 'marks_exam_section_idx');
        });

        Schema::create('result_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'exam_id', 'section_id'], 'result_publications_unique');
            $table->index(['tenant_id', 'exam_id', 'published_at'], 'result_pub_exam_published_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_publications');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('grading_schemes');
    }
};

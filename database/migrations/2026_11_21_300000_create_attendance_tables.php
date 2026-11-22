<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status', 32)->default('present'); // present|absent|late|excused|leave
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'attendance_date'], 'student_daily_attendance_unique');
            $table->index(['tenant_id', 'school_class_id', 'section_id', 'attendance_date'], 'student_att_class_date_idx');
            $table->index(['tenant_id', 'attendance_date']);
        });

        Schema::create('period_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_routine_id')->constrained('class_routines')->cascadeOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('periods')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status', 32)->default('present'); // present|absent|late|excused|leave
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'class_routine_id', 'attendance_date'], 'period_attendance_unique');
            $table->index(['tenant_id', 'class_routine_id', 'attendance_date'], 'period_att_routine_date_idx');
            $table->index(['tenant_id', 'attendance_date']);
        });

        Schema::create('attendance_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_routine_id')->constrained('class_routines')->cascadeOnDelete();
            $table->string('token', 64);
            $table->date('valid_on');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'token']);
            $table->index(['tenant_id', 'class_routine_id', 'valid_on'], 'att_qr_routine_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_qr_tokens');
        Schema::dropIfExists('period_attendances');
        Schema::dropIfExists('student_attendances');
    }
};

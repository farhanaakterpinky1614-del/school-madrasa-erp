<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('admission_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_form_template_id')->constrained('admission_form_templates')->cascadeOnDelete();
            $table->string('field_key', 64);
            $table->string('label');
            $table->string('field_type', 32)->default('text'); // text|number|date|select|textarea|checkbox|file
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('applies_to', 16)->default('both'); // online|offline|both
            $table->timestamps();

            $table->unique(['admission_form_template_id', 'field_key'], 'adm_form_field_key_unique');
            $table->index(['tenant_id', 'admission_form_template_id']);
        });

        Schema::create('admission_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('admission_form_template_id')->nullable()->constrained('admission_form_templates')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->string('public_slug', 64);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('allow_online')->default(true);
            $table->boolean('allow_offline')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'public_slug']);
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('student_code', 64);
            $table->string('admission_number', 64)->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('name_bn')->nullable();
            $table->string('gender', 16)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 8)->nullable();
            $table->string('religion', 64)->nullable();
            $table->string('nationality', 64)->nullable()->default('Bangladeshi');
            $table->string('birth_certificate_no', 64)->nullable();
            $table->string('nid_no', 64)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('division')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 32)->default('active'); // active|withdrawn|alumni|transferred
            $table->date('enrolled_on')->nullable();
            $table->date('withdrawn_on')->nullable();
            $table->date('alumni_on')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->json('custom_fields')->nullable();
            $table->text('notes')->nullable();
            $table->string('qr_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_code']);
            $table->unique(['tenant_id', 'admission_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'last_name', 'first_name']);
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('nid_no', 64)->nullable();
            $table->string('occupation')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'name']);
        });

        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship', 32)->default('guardian'); // father|mother|guardian|other
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_emergency')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->timestamps();

            $table->unique(['guardian_id', 'student_id']);
            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('doc_type', 64)->default('other');
            $table->string('title');
            $table->string('file_path');
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->restrictOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('roll_number', 32)->nullable();
            $table->string('status', 32)->default('active'); // active|completed|transferred|withdrawn
            $table->boolean('is_current')->default(true);
            $table->date('enrolled_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'academic_year_id', 'school_class_id']);
            $table->index(['tenant_id', 'student_id', 'is_current']);
            $table->unique(['tenant_id', 'academic_year_id', 'school_class_id', 'section_id', 'roll_number'], 'enrollment_roll_unique');
        });

        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('from_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            $table->foreignId('to_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            $table->foreignId('from_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('to_class_id')->constrained('school_classes')->restrictOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->date('transferred_on');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            $table->foreignId('to_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            $table->foreignId('from_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('to_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('from_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('to_class_id')->constrained('school_classes')->restrictOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('status', 32)->default('completed'); // pending|completed|failed
            $table->date('promoted_on')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_session_id')->constrained('admission_sessions')->cascadeOnDelete();
            $table->string('application_number', 64);
            $table->string('channel', 16)->default('offline'); // online|offline
            $table->string('status', 32)->default('submitted'); // draft|submitted|under_review|approved|rejected|enrolled|withdrawn
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('name_bn')->nullable();
            $table->string('gender', 16)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 32)->nullable();
            $table->string('guardian_relation', 32)->nullable();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->json('form_payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_notes')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'application_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'admission_session_id']);
        });

        Schema::create('admission_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->string('doc_type', 64)->default('other');
            $table->string('title');
            $table->string('file_path');
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'admission_application_id'], 'adm_app_docs_tenant_app_idx');
        });

        Schema::create('student_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('status', 32)->default('preview'); // preview|imported|failed
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->json('preview_rows')->nullable();
            $table->json('preview_errors')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_import_batches');
        Schema::dropIfExists('admission_application_documents');
        Schema::dropIfExists('admission_applications');
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('student_transfers');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('guardian_student');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('students');
        Schema::dropIfExists('admission_sessions');
        Schema::dropIfExists('admission_form_fields');
        Schema::dropIfExists('admission_form_templates');
    }
};

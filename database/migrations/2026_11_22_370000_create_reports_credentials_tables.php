<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('card_type', 16)->default('student'); // student|staff
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_qr')->default(true);
            $table->string('primary_color', 16)->nullable();
            $table->string('secondary_color', 16)->nullable();
            $table->string('footer_text')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'card_type'], 'id_card_templates_type_idx');
            $table->index(['tenant_id', 'is_default'], 'id_card_templates_default_idx');
        });

        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 64);
            $table->string('certificate_type', 32)->default('completion'); // transfer|completion|character
            $table->string('title');
            $table->text('body');
            $table->string('seal_label')->nullable();
            $table->string('signature_label')->nullable();
            $table->boolean('show_qr')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('placeholders_help')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'certificate_templates_tenant_code_unique');
            $table->index(['tenant_id', 'certificate_type'], 'certificate_templates_type_idx');
            $table->index(['tenant_id', 'is_active'], 'certificate_templates_active_idx');
        });

        Schema::create('certificate_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certificate_template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('issue_number', 64);
            $table->string('status', 16)->default('issued'); // issued|revoked
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('placeholders')->nullable();
            $table->string('qr_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'issue_number'], 'certificate_issues_tenant_number_unique');
            $table->index(['tenant_id', 'status'], 'certificate_issues_status_idx');
            $table->index(['tenant_id', 'student_id'], 'certificate_issues_student_idx');
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('report_type', 64); // students_roster|fee_invoices|attendance_summary
            $table->string('format', 8)->default('xlsx'); // xlsx|csv
            $table->string('status', 16)->default('queued'); // queued|processing|completed|failed
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->json('filters')->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'report_exports_status_idx');
            $table->index(['tenant_id', 'report_type'], 'report_exports_type_idx');
            $table->index(['tenant_id', 'created_at'], 'report_exports_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('certificate_issues');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('id_card_templates');
    }
};

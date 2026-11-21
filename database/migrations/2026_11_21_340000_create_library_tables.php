<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('fine_mode', 16)->default('daily'); // daily|fixed
            $table->decimal('fine_daily_rate', 12, 2)->default(10);
            $table->decimal('fine_fixed_amount', 12, 2)->default(50);
            $table->unsignedSmallInteger('default_loan_days')->default(14);
            $table->timestamps();

            $table->unique('tenant_id', 'library_settings_tenant_unique');
        });

        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn', 32)->nullable();
            $table->string('category', 128)->nullable();
            $table->string('subject_tags', 255)->nullable();
            $table->string('publisher')->nullable();
            $table->string('language', 8)->default('en'); // en|bn|ar
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'title'], 'library_books_title_idx');
            $table->index(['tenant_id', 'isbn'], 'library_books_isbn_idx');
            $table->index(['tenant_id', 'category'], 'library_books_category_idx');
        });

        Schema::create('library_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('accession_no', 64);
            $table->string('barcode', 64)->nullable();
            $table->string('condition', 32)->default('good'); // new|good|fair|poor
            $table->string('status', 32)->default('available'); // available|issued|lost|damaged
            $table->string('shelf_location', 128)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'accession_no'], 'library_copies_tenant_accession_unique');
            $table->index(['tenant_id', 'library_book_id', 'status'], 'library_copies_book_status_idx');
            $table->index(['tenant_id', 'barcode'], 'library_copies_barcode_idx');
        });

        Schema::create('library_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_copy_id')->constrained('library_copies')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('borrower_type', 16)->default('student'); // student|staff
            $table->date('issued_on');
            $table->date('due_on');
            $table->date('returned_on')->nullable();
            $table->string('status', 32)->default('issued'); // issued|returned|lost
            $table->boolean('is_overdue')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'due_on'], 'library_issues_status_due_idx');
            $table->index(['tenant_id', 'student_id'], 'library_issues_student_idx');
            $table->index(['tenant_id', 'staff_id'], 'library_issues_staff_idx');
            $table->index(['tenant_id', 'library_copy_id'], 'library_issues_copy_idx');
        });

        Schema::create('library_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_issue_id')->constrained('library_issues')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->string('status', 32)->default('pending'); // pending|paid|waived
            $table->string('fine_mode', 16)->default('daily'); // daily|fixed
            $table->decimal('rate_applied', 12, 2)->default(0);
            $table->unsignedInteger('days_overdue')->default(0);
            $table->date('calculated_on')->nullable();
            $table->date('settled_on')->nullable();
            $table->string('fees_note', 255)->nullable(); // optional light link note to Fees module
            $table->text('notes')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'library_fines_status_idx');
            $table->index(['tenant_id', 'library_issue_id'], 'library_fines_issue_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_fines');
        Schema::dropIfExists('library_issues');
        Schema::dropIfExists('library_copies');
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('library_settings');
    }
};

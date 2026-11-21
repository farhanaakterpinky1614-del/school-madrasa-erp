<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 64);
            $table->string('kind', 32)->default('misc'); // tuition|exam|hostel|transport|misc
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'fee_categories_tenant_code_unique');
            $table->index(['tenant_id', 'kind']);
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 32)->default('monthly'); // monthly|term|annual|one_time
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'academic_year_id', 'school_class_id'], 'fee_structures_year_class_idx');
            $table->index(['tenant_id', 'fee_category_id'], 'fee_structures_category_idx');
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
            $table->string('invoice_number', 64);
            $table->string('title');
            $table->string('status', 32)->default('draft'); // draft|issued|partial|paid|cancelled
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number'], 'fee_invoices_tenant_number_unique');
            $table->index(['tenant_id', 'student_id', 'status'], 'fee_invoices_student_status_idx');
            $table->index(['tenant_id', 'due_date'], 'fee_invoices_due_idx');
        });

        Schema::create('fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->string('type', 32); // percent|fixed|scholarship|waiver
            $table->string('label');
            $table->decimal('percent', 5, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'fee_invoice_id'], 'fee_discounts_invoice_idx');
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->string('payment_number', 64);
            $table->decimal('amount', 12, 2);
            $table->string('method', 32)->default('cash'); // cash|bank|bkash|nagad
            $table->date('paid_on');
            $table->string('reference', 128)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'payment_number'], 'fee_payments_tenant_number_unique');
            $table->index(['tenant_id', 'fee_invoice_id'], 'fee_payments_invoice_idx');
            $table->index(['tenant_id', 'paid_on'], 'fee_payments_paid_on_idx');
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 64);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'expense_categories_tenant_code_unique');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('spent_on');
            $table->string('payment_method', 32)->default('cash'); // cash|bank|bkash|nagad
            $table->string('reference', 128)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'spent_on'], 'expenses_spent_on_idx');
            $table->index(['tenant_id', 'expense_category_id'], 'expenses_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_discounts');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
    }
};

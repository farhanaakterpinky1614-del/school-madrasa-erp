<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 16 — Hardening & Enterprise: webhooks, domain verify metadata, hot indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('target_url', 500);
            $table->string('secret', 128)->nullable();
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_delivered_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active'], 'webhook_subs_tenant_active_idx');
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webhook_subscription_id')->constrained('webhook_subscriptions')->cascadeOnDelete();
            $table->string('event', 64);
            $table->json('payload');
            $table->string('status', 16)->default('pending'); // pending|stubbed|failed
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event', 'created_at'], 'webhook_del_tenant_event_idx');
            $table->index(['tenant_id', 'status'], 'webhook_del_tenant_status_idx');
            $table->index(['webhook_subscription_id', 'created_at'], 'webhook_del_sub_created_idx');
        });

        // Hot-path performance indexes (idempotent-ish: skip if already present via Schema).
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->index(['tenant_id', 'method', 'paid_on'], 'fee_payments_method_paid_idx');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'attendance_date'], 'student_att_status_date_idx');
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'updated_at'], 'adm_apps_status_updated_idx');
        });

        Schema::table('tenant_usage_metrics', function (Blueprint $table) {
            $table->index(['tenant_id', 'metric_key'], 'usage_metrics_tenant_key_idx');
        });

        Schema::table('tenant_domains', function (Blueprint $table) {
            $table->index(['tenant_id', 'verification_status'], 'tenant_domains_verify_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_domains', function (Blueprint $table) {
            $table->dropIndex('tenant_domains_verify_idx');
        });

        Schema::table('tenant_usage_metrics', function (Blueprint $table) {
            $table->dropIndex('usage_metrics_tenant_key_idx');
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropIndex('adm_apps_status_updated_idx');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropIndex('student_att_status_date_idx');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex('fee_payments_method_paid_idx');
        });

        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_subscriptions');
    }
};

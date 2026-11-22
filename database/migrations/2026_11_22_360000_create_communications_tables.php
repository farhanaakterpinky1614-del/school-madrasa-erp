<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sms_driver', 16)->default('stub'); // stub|log|ssl|bulk
            $table->string('email_driver', 16)->default('log'); // stub|log|mail
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('tenant_id', 'communication_settings_tenant_unique');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('audience_type', 16)->default('all'); // all|roles|class
            $table->json('audience_roles')->nullable();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('status', 16)->default('draft'); // draft|published|unpublished
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'announcements_tenant_status_idx');
            $table->index(['tenant_id', 'audience_type'], 'announcements_audience_idx');
            $table->index(['tenant_id', 'scheduled_at'], 'announcements_scheduled_idx');
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 64);
            $table->string('channel', 16); // sms|email
            $table->string('subject')->nullable();
            $table->text('body');
            $table->text('placeholders_help')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'notification_templates_tenant_code_unique');
            $table->index(['tenant_id', 'channel'], 'notification_templates_channel_idx');
            $table->index(['tenant_id', 'is_active'], 'notification_templates_active_idx');
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('channel', 16); // sms|email|in_app
            $table->string('recipient');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 16)->default('pending'); // pending|sent|failed|stubbed
            $table->string('driver', 16)->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'notification_logs_status_idx');
            $table->index(['tenant_id', 'channel'], 'notification_logs_channel_idx');
            $table->index(['tenant_id', 'created_at'], 'notification_logs_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('communication_settings');
    }
};

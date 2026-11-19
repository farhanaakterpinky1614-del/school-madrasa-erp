<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_platform_admin')->default(false)->after('password');
            $table->string('user_type', 32)->default('tenant_admin')->after('is_platform_admin');
            $table->index(['tenant_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['is_platform_admin', 'user_type']);
        });
    }
};

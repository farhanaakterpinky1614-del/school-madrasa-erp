<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foundation for class/section-scoped authz (Teacher own-scope).
 * class_id is nullable until Academics module (Phase 04+) lands.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_id')->nullable()->index(); // Phase 04+
            $table->string('permission'); // e.g. academics.class.view
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'class_id', 'permission'], 'class_perms_unique');
            $table->index(['tenant_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_permissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foundation for multi-branch authz (Phase 04+ campuses/branches).
 * branch_id is nullable until the Academics/campus module lands.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index(); // Phase 04+
            $table->string('permission'); // e.g. branches.branch.view
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'branch_id', 'permission'], 'branch_perms_unique');
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_permissions');
    }
};

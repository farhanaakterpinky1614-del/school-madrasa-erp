<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 63)->unique();
            $table->string('institution_type', 32);
            $table->string('status', 32)->default('pending')->index();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('timezone', 64)->default('Asia/Dhaka');
            $table->string('locale_default', 8)->default('en');
            $table->string('currency', 8)->default('BDT');
            $table->json('settings')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

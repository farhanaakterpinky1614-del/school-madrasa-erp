<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('host')->unique();
            $table->string('type', 16)->default('subdomain'); // subdomain | custom
            $table->boolean('is_primary')->default(false);
            $table->string('verification_status', 16)->default('verified');
            $table->string('verification_token', 64)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
            // TODO(Phase 15): full DNS TXT / HTTP verification workflow before activating custom domains.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
    }
};

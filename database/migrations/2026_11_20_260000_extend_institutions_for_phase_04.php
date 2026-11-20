<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('eiin', 32)->nullable()->after('institution_type');
            $table->string('board', 120)->nullable()->after('eiin');
            $table->string('website')->nullable()->after('phone');
            $table->string('motto')->nullable()->after('website');
            $table->unsignedSmallInteger('founded_year')->nullable()->after('motto');
            $table->string('logo_path')->nullable()->after('founded_year');
            $table->string('primary_color', 16)->nullable()->after('logo_path');
            $table->string('secondary_color', 16)->nullable()->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'eiin',
                'board',
                'website',
                'motto',
                'founded_year',
                'logo_path',
                'primary_color',
                'secondary_color',
            ]);
        });
    }
};

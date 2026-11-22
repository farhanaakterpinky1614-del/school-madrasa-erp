<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('plate_number', 32);
            $table->string('type', 16)->default('bus'); // bus|van
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 32)->nullable();
            $table->string('status', 16)->default('active'); // active|inactive|maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'plate_number'], 'vehicles_tenant_plate_unique');
            $table->index(['tenant_id', 'status'], 'vehicles_tenant_status_idx');
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->text('stops')->nullable(); // simple text / JSON list of stops
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->decimal('monthly_fee_amount', 12, 2)->default(0);
            $table->string('currency', 8)->default('BDT');
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->nullOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name'], 'transport_routes_name_idx');
            $table->index(['tenant_id', 'is_active'], 'transport_routes_active_idx');
        });

        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->string('pickup_stop')->nullable();
            $table->string('status', 16)->default('active'); // active|inactive
            $table->date('assigned_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('fees_note', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'transport_assignments_status_idx');
            $table->index(['tenant_id', 'student_id', 'status'], 'transport_assignments_student_idx');
            $table->index(['tenant_id', 'transport_route_id'], 'transport_assignments_route_idx');
        });

        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->string('gender_type', 16)->default('mixed'); // male|female|mixed
            $table->text('capacity_notes')->nullable();
            $table->decimal('monthly_fee_amount', 12, 2)->nullable();
            $table->string('currency', 8)->default('BDT');
            $table->foreignId('fee_category_id')->nullable()->constrained('fee_categories')->nullOnDelete();
            $table->foreignId('fee_structure_id')->nullable()->constrained('fee_structures')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name'], 'hostels_name_idx');
            $table->index(['tenant_id', 'gender_type'], 'hostels_gender_idx');
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->string('room_number', 32);
            $table->string('floor', 32)->nullable();
            $table->unsignedSmallInteger('bed_count')->default(2);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'hostel_id', 'room_number'], 'hostel_rooms_tenant_hostel_room_unique');
            $table->index(['tenant_id', 'hostel_id'], 'hostel_rooms_hostel_idx');
        });

        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->string('label', 32);
            $table->string('status', 16)->default('available'); // available|occupied|maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'hostel_room_id', 'label'], 'hostel_beds_tenant_room_label_unique');
            $table->index(['tenant_id', 'status'], 'hostel_beds_status_idx');
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('hostel_bed_id')->constrained('hostel_beds')->cascadeOnDelete();
            $table->date('check_in_on');
            $table->date('check_out_on')->nullable();
            $table->string('status', 16)->default('active'); // active|checked_out
            $table->string('fees_note', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'hostel_allocations_status_idx');
            $table->index(['tenant_id', 'student_id', 'status'], 'hostel_allocations_student_idx');
            $table->index(['tenant_id', 'hostel_bed_id', 'status'], 'hostel_allocations_bed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
        Schema::dropIfExists('transport_assignments');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('vehicles');
    }
};

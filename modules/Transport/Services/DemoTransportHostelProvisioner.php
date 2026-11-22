<?php

namespace Modules\Transport\Services;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Modules\Fees\Models\FeeCategory;
use Modules\Fees\Models\FeeStructure;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelAllocation;
use Modules\Hostel\Models\HostelBed;
use Modules\Hostel\Models\HostelRoom;
use Modules\Hostel\Services\HostelAllocationService;
use Modules\Students\Models\Student;
use Modules\Tenancy\Models\Tenant;
use Modules\Transport\Models\TransportAssignment;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;

/**
 * Seeds demo transport vehicles/routes/assignments and hostel rooms/beds/allocations.
 */
class DemoTransportHostelProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected TransportAssignmentService $transportAssignments,
        protected HostelAllocationService $hostelAllocations,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            $transportFee = FeeCategory::query()
                ->where(function ($q) {
                    $q->where('kind', FeeCategory::KIND_TRANSPORT)
                        ->orWhere('code', 'TRANSPORT');
                })
                ->first();
            $hostelFee = FeeCategory::query()
                ->where(function ($q) {
                    $q->where('kind', FeeCategory::KIND_HOSTEL)
                        ->orWhere('code', 'HOSTEL');
                })
                ->first();

            $transportStructure = $transportFee
                ? FeeStructure::query()->where('fee_category_id', $transportFee->id)->orderBy('id')->first()
                : null;
            $hostelStructure = $hostelFee
                ? FeeStructure::query()->where('fee_category_id', $hostelFee->id)->orderBy('id')->first()
                : null;

            $bus = Vehicle::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'plate_number' => 'DHAKA-METRO-GA-11-1234',
                ],
                [
                    'type' => Vehicle::TYPE_BUS,
                    'capacity' => 40,
                    'driver_name' => 'Karim Hossain',
                    'driver_phone' => '01711000011',
                    'status' => Vehicle::STATUS_ACTIVE,
                    'notes' => 'Demo campus bus',
                ]
            );

            $van = Vehicle::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'plate_number' => 'DHAKA-METRO-CHA-22-5678',
                ],
                [
                    'type' => Vehicle::TYPE_VAN,
                    'capacity' => 12,
                    'driver_name' => 'Rafiq Uddin',
                    'driver_phone' => '01722000022',
                    'status' => Vehicle::STATUS_ACTIVE,
                    'notes' => 'Demo staff/student van',
                ]
            );

            $routeA = TransportRoute::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'RT-NORTH',
                ],
                [
                    'name' => 'North Dhaka Loop',
                    'stops' => "Uttara\nAirport\nBanani\nCampus",
                    'vehicle_id' => $bus->id,
                    'monthly_fee_amount' => 1500,
                    'currency' => 'BDT',
                    'fee_category_id' => $transportFee?->id,
                    'fee_structure_id' => $transportStructure?->id,
                    'is_active' => true,
                    'notes' => 'Demo morning/afternoon loop',
                ]
            );

            TransportRoute::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'RT-SOUTH',
                ],
                [
                    'name' => 'South Dhaka Express',
                    'stops' => json_encode(['Dhanmondi', 'Lalmatia', 'Campus'], JSON_THROW_ON_ERROR),
                    'vehicle_id' => $van->id,
                    'monthly_fee_amount' => 1200,
                    'currency' => 'BDT',
                    'fee_category_id' => $transportFee?->id,
                    'fee_structure_id' => $transportStructure?->id,
                    'is_active' => true,
                    'notes' => 'Demo compact van route',
                ]
            );

            $boysHostel = Hostel::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'H-BOYS',
                ],
                [
                    'name' => 'Al-Noor Boys Hostel',
                    'gender_type' => Hostel::GENDER_MALE,
                    'capacity_notes' => 'Two floors, shared baths; ~24 beds.',
                    'monthly_fee_amount' => 3500,
                    'currency' => 'BDT',
                    'fee_category_id' => $hostelFee?->id,
                    'fee_structure_id' => $hostelStructure?->id,
                    'is_active' => true,
                    'notes' => 'Demo boys boarding',
                ]
            );

            $girlsHostel = Hostel::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'H-GIRLS',
                ],
                [
                    'name' => 'Rahmah Girls Hostel',
                    'gender_type' => Hostel::GENDER_FEMALE,
                    'capacity_notes' => 'Ground floor only; supervised evening study.',
                    'monthly_fee_amount' => 3600,
                    'currency' => 'BDT',
                    'fee_category_id' => $hostelFee?->id,
                    'fee_structure_id' => $hostelStructure?->id,
                    'is_active' => true,
                    'notes' => 'Demo girls boarding',
                ]
            );

            $room101 = HostelRoom::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'hostel_id' => $boysHostel->id,
                    'room_number' => '101',
                ],
                [
                    'floor' => '1',
                    'bed_count' => 2,
                    'is_active' => true,
                    'notes' => 'Demo twin room',
                ]
            );

            $room102 = HostelRoom::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'hostel_id' => $boysHostel->id,
                    'room_number' => '102',
                ],
                [
                    'floor' => '1',
                    'bed_count' => 2,
                    'is_active' => true,
                ]
            );

            HostelRoom::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'hostel_id' => $girlsHostel->id,
                    'room_number' => 'A1',
                ],
                [
                    'floor' => 'G',
                    'bed_count' => 2,
                    'is_active' => true,
                ]
            );

            $beds = [];
            foreach ([$room101, $room102] as $room) {
                foreach (['A', 'B'] as $label) {
                    $beds[] = HostelBed::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'hostel_room_id' => $room->id,
                            'label' => $label,
                        ],
                        [
                            'status' => HostelBed::STATUS_AVAILABLE,
                            'notes' => null,
                        ]
                    );
                }
            }

            $students = Student::query()
                ->where('status', Student::STATUS_ACTIVE)
                ->orderBy('id')
                ->limit(4)
                ->get();

            if ($students->isEmpty()) {
                return;
            }

            $hasTransport = TransportAssignment::query()
                ->where('student_id', $students[0]->id)
                ->where('status', TransportAssignment::STATUS_ACTIVE)
                ->exists();
            if (! $hasTransport) {
                $this->transportAssignments->assign([
                    'student_id' => $students[0]->id,
                    'transport_route_id' => $routeA->id,
                    'pickup_stop' => 'Uttara',
                    'assigned_on' => now('Asia/Dhaka')->subDays(10)->toDateString(),
                    'fees_note' => 'Optional link: TRANSPORT fee category',
                    'notes' => 'Demo active transport assignment',
                ]);
            }

            if ($students->count() >= 2 && isset($beds[0]) && $beds[0]->fresh()->isAvailable()) {
                $hasHostel = HostelAllocation::query()
                    ->where('student_id', $students[1]->id)
                    ->where('status', HostelAllocation::STATUS_ACTIVE)
                    ->exists();
                if (! $hasHostel) {
                    $this->hostelAllocations->allocate([
                        'student_id' => $students[1]->id,
                        'hostel_bed_id' => $beds[0]->id,
                        'check_in_on' => now('Asia/Dhaka')->subDays(20)->toDateString(),
                        'fees_note' => 'Optional link: HOSTEL fee category',
                        'notes' => 'Demo active hostel allocation',
                    ]);
                }
            }

            if (isset($beds[3]) && $beds[3]->fresh()->isAvailable()) {
                $beds[3]->update(['status' => HostelBed::STATUS_MAINTENANCE, 'notes' => 'Demo maintenance']);
            }
        });
    }
}

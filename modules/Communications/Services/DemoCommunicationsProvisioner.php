<?php

namespace Modules\Communications\Services;

use App\Core\Tenancy\CurrentTenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Academics\Models\SchoolClass;
use Modules\Communications\Models\Announcement;
use Modules\Communications\Models\CommunicationSetting;
use Modules\Communications\Models\NotificationTemplate;
use Modules\Tenancy\Models\Tenant;

/**
 * Seeds demo announcements, templates, gateway settings, and a sample delivery log.
 */
class DemoCommunicationsProvisioner
{
    public function __construct(
        protected CurrentTenant $currentTenant,
        protected NotificationSendService $sender,
        protected AnnouncementService $announcements,
    ) {}

    public function provision(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $this->currentTenant->set($tenant);

            CommunicationSetting::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'sms_driver' => CommunicationSetting::SMS_DRIVER_STUB,
                    'email_driver' => CommunicationSetting::EMAIL_DRIVER_LOG,
                    'sms_enabled' => true,
                    'email_enabled' => true,
                    'notes' => 'Demo gateway stubs — no live SMS/email credentials.',
                ]
            );

            $admin = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', 'like', 'admin%')
                ->orderBy('id')
                ->first();

            $class = SchoolClass::query()->orderBy('id')->first();

            $welcome = Announcement::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Welcome to the new term',
                ],
                [
                    'body' => 'Assalamu alaikum. Classes resume next Sunday. Please check the academic calendar for timings.',
                    'audience_type' => Announcement::AUDIENCE_ALL,
                    'audience_roles' => null,
                    'school_class_id' => null,
                    'status' => Announcement::STATUS_DRAFT,
                    'published_at' => null,
                    'scheduled_at' => null,
                    'created_by' => $admin?->id,
                ]
            );

            if ($welcome->status !== Announcement::STATUS_PUBLISHED) {
                $this->announcements->publish($welcome);
            }

            Announcement::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'title' => 'Teachers — mark attendance daily',
                ],
                [
                    'body' => 'Please complete daily attendance before 10:00 AM Asia/Dhaka.',
                    'audience_type' => Announcement::AUDIENCE_ROLES,
                    'audience_roles' => ['Teacher'],
                    'school_class_id' => null,
                    'status' => Announcement::STATUS_PUBLISHED,
                    'published_at' => now()->subDay(),
                    'scheduled_at' => null,
                    'created_by' => $admin?->id,
                ]
            );

            if ($class) {
                Announcement::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'title' => 'Class notice — '.$class->name,
                    ],
                    [
                        'body' => 'Parents of '.$class->name.': parent-teacher meeting this Thursday after Maghrib.',
                        'audience_type' => Announcement::AUDIENCE_CLASS,
                        'audience_roles' => null,
                        'school_class_id' => $class->id,
                        'status' => Announcement::STATUS_PUBLISHED,
                        'published_at' => now()->subHours(6),
                        'scheduled_at' => null,
                        'created_by' => $admin?->id,
                    ]
                );
            }

            $feeSms = NotificationTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'FEE_DUE_SMS',
                ],
                [
                    'name' => 'Fee due SMS',
                    'channel' => NotificationTemplate::CHANNEL_SMS,
                    'subject' => null,
                    'body' => 'Dear guardian of {{student_name}}, fee amount {{amount}} BDT is due. Please pay at the office.',
                    'placeholders_help' => '{{student_name}}, {{amount}}',
                    'is_active' => true,
                ]
            );

            NotificationTemplate::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'ABSENCE_EMAIL',
                ],
                [
                    'name' => 'Absence email',
                    'channel' => NotificationTemplate::CHANNEL_EMAIL,
                    'subject' => 'Absence notice for {{student_name}}',
                    'body' => "Assalamu alaikum,\n\n{{student_name}} was marked absent today. Please contact the office if this is unexpected.\n\n— {{institution_name}}",
                    'placeholders_help' => '{{student_name}}, {{institution_name}}',
                    'is_active' => true,
                ]
            );

            // Seed one stubbed delivery log if none exist yet.
            if ($feeSms->logs()->doesntExist()) {
                $this->sender->send(
                    channel: NotificationTemplate::CHANNEL_SMS,
                    recipient: '01700000001',
                    template: $feeSms,
                    placeholders: [
                        'student_name' => 'Ayesha Rahman',
                        'amount' => '1500',
                    ],
                    meta: ['source' => 'demo_seed'],
                );
            }
        });
    }
}

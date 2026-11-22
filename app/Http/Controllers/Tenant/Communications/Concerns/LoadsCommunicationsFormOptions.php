<?php

namespace App\Http\Controllers\Tenant\Communications\Concerns;

use Modules\Academics\Models\SchoolClass;
use Modules\Communications\Models\Announcement;
use Modules\Communications\Models\CommunicationSetting;
use Modules\Communications\Models\NotificationLog;
use Modules\Communications\Models\NotificationTemplate;
use Spatie\Permission\Models\Role;

trait LoadsCommunicationsFormOptions
{
    /**
     * @return array<string, mixed>
     */
    protected function communicationsFormOptions(): array
    {
        $tenantId = app(\App\Core\Tenancy\CurrentTenant::class)->id();

        return [
            'audienceTypes' => Announcement::AUDIENCE_TYPES,
            'announcementStatuses' => Announcement::STATUSES,
            'templateChannels' => NotificationTemplate::CHANNELS,
            'logChannels' => NotificationLog::CHANNELS,
            'logStatuses' => NotificationLog::STATUSES,
            'smsDrivers' => CommunicationSetting::SMS_DRIVERS,
            'emailDrivers' => CommunicationSetting::EMAIL_DRIVERS,
            'schoolClasses' => SchoolClass::query()->orderBy('name')->get(['id', 'name']),
            'roleNames' => Role::query()
                ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->pluck('name'),
            'templates' => NotificationTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'channel', 'subject', 'body']),
        ];
    }
}

<?php

namespace Modules\Communications\Gateways;

use Modules\Communications\Contracts\SmsGateway;
use Modules\Communications\Models\NotificationLog;

/**
 * Stub for BulkSMS BD / generic bulk gateways — no live credentials.
 */
class BulkSmsStubGateway implements SmsGateway
{
    public function driverName(): string
    {
        return 'bulk';
    }

    public function send(string $to, string $message): array
    {
        return [
            'status' => NotificationLog::STATUS_STUBBED,
            'error' => null,
            'meta' => [
                'gateway' => 'bulk-sms-stub',
                'to' => $to,
                'note' => 'No live API call; configure credentials in production.',
            ],
        ];
    }
}

<?php

namespace Modules\Communications\Gateways;

use Modules\Communications\Contracts\SmsGateway;
use Modules\Communications\Models\NotificationLog;

/**
 * Stub for SSL Wireless / similar BD SMS APIs — no live credentials.
 */
class SslSmsStubGateway implements SmsGateway
{
    public function driverName(): string
    {
        return 'ssl';
    }

    public function send(string $to, string $message): array
    {
        return [
            'status' => NotificationLog::STATUS_STUBBED,
            'error' => null,
            'meta' => [
                'gateway' => 'ssl-wireless-stub',
                'to' => $to,
                'note' => 'No live API call; configure credentials in production.',
            ],
        ];
    }
}

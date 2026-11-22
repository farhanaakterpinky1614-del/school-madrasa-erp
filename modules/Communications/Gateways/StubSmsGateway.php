<?php

namespace Modules\Communications\Gateways;

use Modules\Communications\Contracts\SmsGateway;
use Modules\Communications\Models\NotificationLog;

class StubSmsGateway implements SmsGateway
{
    public function driverName(): string
    {
        return 'stub';
    }

    public function send(string $to, string $message): array
    {
        return [
            'status' => NotificationLog::STATUS_STUBBED,
            'error' => null,
            'meta' => [
                'gateway' => 'bangladesh-sms-stub',
                'to' => $to,
                'length' => mb_strlen($message),
            ],
        ];
    }
}

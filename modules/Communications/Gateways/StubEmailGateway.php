<?php

namespace Modules\Communications\Gateways;

use Modules\Communications\Contracts\EmailGateway;
use Modules\Communications\Models\NotificationLog;

class StubEmailGateway implements EmailGateway
{
    public function driverName(): string
    {
        return 'stub';
    }

    public function send(string $to, string $subject, string $body): array
    {
        return [
            'status' => NotificationLog::STATUS_STUBBED,
            'error' => null,
            'meta' => [
                'gateway' => 'email-stub',
                'to' => $to,
                'subject' => $subject,
            ],
        ];
    }
}

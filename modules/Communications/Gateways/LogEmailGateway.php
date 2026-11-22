<?php

namespace Modules\Communications\Gateways;

use Illuminate\Support\Facades\Log;
use Modules\Communications\Contracts\EmailGateway;
use Modules\Communications\Models\NotificationLog;

class LogEmailGateway implements EmailGateway
{
    public function driverName(): string
    {
        return 'log';
    }

    public function send(string $to, string $subject, string $body): array
    {
        Log::info('communications.email.log_driver', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ]);

        return [
            'status' => NotificationLog::STATUS_SENT,
            'error' => null,
            'meta' => ['gateway' => 'log'],
        ];
    }
}

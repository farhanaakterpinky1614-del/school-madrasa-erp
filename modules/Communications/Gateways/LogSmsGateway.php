<?php

namespace Modules\Communications\Gateways;

use Illuminate\Support\Facades\Log;
use Modules\Communications\Contracts\SmsGateway;
use Modules\Communications\Models\NotificationLog;

class LogSmsGateway implements SmsGateway
{
    public function driverName(): string
    {
        return 'log';
    }

    public function send(string $to, string $message): array
    {
        Log::info('communications.sms.log_driver', [
            'to' => $to,
            'message' => $message,
        ]);

        return [
            'status' => NotificationLog::STATUS_SENT,
            'error' => null,
            'meta' => ['gateway' => 'log'],
        ];
    }
}

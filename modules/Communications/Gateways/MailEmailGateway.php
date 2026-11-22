<?php

namespace Modules\Communications\Gateways;

use Illuminate\Support\Facades\Mail;
use Modules\Communications\Contracts\EmailGateway;
use Modules\Communications\Models\NotificationLog;
use Throwable;

/**
 * Uses Laravel mailer (typically log/array in local) — no paid gateway required.
 */
class MailEmailGateway implements EmailGateway
{
    public function driverName(): string
    {
        return 'mail';
    }

    public function send(string $to, string $subject, string $body): array
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return [
                'status' => NotificationLog::STATUS_SENT,
                'error' => null,
                'meta' => ['gateway' => 'mail'],
            ];
        } catch (Throwable $e) {
            return [
                'status' => NotificationLog::STATUS_FAILED,
                'error' => $e->getMessage(),
                'meta' => ['gateway' => 'mail'],
            ];
        }
    }
}

<?php

namespace Modules\Communications\Services;

use Modules\Communications\Models\NotificationLog;
use Modules\Communications\Models\NotificationTemplate;
use RuntimeException;

class NotificationSendService
{
    public function __construct(protected GatewayManager $gateways) {}

    /**
     * Compose from template (or raw body) and write a delivery log via stub/log/mail drivers.
     *
     * @param  array<string, string|int|float|null>  $placeholders
     * @param  array<string, mixed>  $meta
     */
    public function send(
        string $channel,
        string $recipient,
        ?NotificationTemplate $template = null,
        array $placeholders = [],
        ?string $subject = null,
        ?string $body = null,
        ?int $recipientUserId = null,
        array $meta = [],
    ): NotificationLog {
        $settings = $this->gateways->settings();

        if ($channel === NotificationLog::CHANNEL_SMS && ! $settings->sms_enabled) {
            throw new RuntimeException('SMS channel is disabled for this tenant.');
        }

        if ($channel === NotificationLog::CHANNEL_EMAIL && ! $settings->email_enabled) {
            throw new RuntimeException('Email channel is disabled for this tenant.');
        }

        if ($template !== null) {
            if ($template->channel !== $channel) {
                throw new RuntimeException('Template channel does not match send channel.');
            }
            $rendered = $template->render($placeholders);
            $subject = $rendered['subject'] ?? $subject;
            $body = $rendered['body'];
        }

        if ($body === null || trim($body) === '') {
            throw new RuntimeException('Message body is required.');
        }

        if ($channel === NotificationLog::CHANNEL_EMAIL && ($subject === null || trim($subject) === '')) {
            $subject = 'Notification';
        }

        $log = NotificationLog::query()->create([
            'notification_template_id' => $template?->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'recipient_user_id' => $recipientUserId,
            'subject' => $subject,
            'body' => $body,
            'status' => NotificationLog::STATUS_PENDING,
            'driver' => null,
            'meta' => $meta ?: null,
        ]);

        $result = match ($channel) {
            NotificationLog::CHANNEL_SMS => $this->gateways->sms()->send($recipient, $body),
            NotificationLog::CHANNEL_EMAIL => $this->gateways->email()->send($recipient, (string) $subject, $body),
            default => throw new RuntimeException('Unsupported channel: '.$channel),
        };

        $driver = match ($channel) {
            NotificationLog::CHANNEL_SMS => $this->gateways->sms()->driverName(),
            NotificationLog::CHANNEL_EMAIL => $this->gateways->email()->driverName(),
            default => null,
        };

        $log->update([
            'status' => $result['status'],
            'driver' => $driver,
            'error_message' => $result['error'] ?? null,
            'meta' => array_merge($log->meta ?? [], $result['meta'] ?? []),
            'sent_at' => in_array($result['status'], [
                NotificationLog::STATUS_SENT,
                NotificationLog::STATUS_STUBBED,
            ], true) ? now() : null,
        ]);

        return $log->fresh();
    }
}

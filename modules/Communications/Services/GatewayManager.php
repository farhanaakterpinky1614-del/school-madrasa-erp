<?php

namespace Modules\Communications\Services;

use Modules\Communications\Contracts\EmailGateway;
use Modules\Communications\Contracts\SmsGateway;
use Modules\Communications\Gateways\BulkSmsStubGateway;
use Modules\Communications\Gateways\LogEmailGateway;
use Modules\Communications\Gateways\LogSmsGateway;
use Modules\Communications\Gateways\MailEmailGateway;
use Modules\Communications\Gateways\SslSmsStubGateway;
use Modules\Communications\Gateways\StubEmailGateway;
use Modules\Communications\Gateways\StubSmsGateway;
use Modules\Communications\Models\CommunicationSetting;

class GatewayManager
{
    public function settings(): CommunicationSetting
    {
        return CommunicationSetting::query()->firstOrCreate(
            [],
            [
                'sms_driver' => CommunicationSetting::SMS_DRIVER_STUB,
                'email_driver' => CommunicationSetting::EMAIL_DRIVER_LOG,
                'sms_enabled' => true,
                'email_enabled' => true,
            ]
        );
    }

    public function sms(): SmsGateway
    {
        $settings = $this->settings();

        return match ($settings->sms_driver) {
            CommunicationSetting::SMS_DRIVER_LOG => new LogSmsGateway,
            CommunicationSetting::SMS_DRIVER_SSL => new SslSmsStubGateway,
            CommunicationSetting::SMS_DRIVER_BULK => new BulkSmsStubGateway,
            default => new StubSmsGateway,
        };
    }

    public function email(): EmailGateway
    {
        $settings = $this->settings();

        return match ($settings->email_driver) {
            CommunicationSetting::EMAIL_DRIVER_MAIL => new MailEmailGateway,
            CommunicationSetting::EMAIL_DRIVER_STUB => new StubEmailGateway,
            default => new LogEmailGateway,
        };
    }
}

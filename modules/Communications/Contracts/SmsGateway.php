<?php

namespace Modules\Communications\Contracts;

interface SmsGateway
{
    public function driverName(): string;

    /**
     * @return array{status: string, error: ?string, meta?: array<string, mixed>}
     */
    public function send(string $to, string $message): array;
}

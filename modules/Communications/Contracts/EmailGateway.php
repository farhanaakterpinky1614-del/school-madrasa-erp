<?php

namespace Modules\Communications\Contracts;

interface EmailGateway
{
    public function driverName(): string;

    /**
     * @return array{status: string, error: ?string, meta?: array<string, mixed>}
     */
    public function send(string $to, string $subject, string $body): array;
}

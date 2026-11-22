<?php

namespace Modules\Reports\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QrCredentialService
{
    public function dataUri(string $payload, int $size = 220): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size($size)
            ->margin(8)
            ->build();

        return $result->getDataUri();
    }
}

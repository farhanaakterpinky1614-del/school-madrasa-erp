<?php

namespace Modules\Students\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Modules\Students\Models\Student;

class QrIdCardService
{
    public function __construct(protected StudentIdGenerator $ids) {}

    public function ensurePayload(Student $student): Student
    {
        if (! $student->qr_payload) {
            $student->update(['qr_payload' => $this->ids->qrPayload($student)]);
            $student->refresh();
        }

        return $student;
    }

    public function dataUri(Student $student): string
    {
        $student = $this->ensurePayload($student);

        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($student->qr_payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(220)
            ->margin(8)
            ->build();

        return $result->getDataUri();
    }
}

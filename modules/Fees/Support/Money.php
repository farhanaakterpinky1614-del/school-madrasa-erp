<?php

namespace Modules\Fees\Support;

class Money
{
    public static function formatBdt(float|int|string|null $amount): string
    {
        $value = round((float) ($amount ?? 0), 2);

        return 'BDT '.number_format($value, 2, '.', ',');
    }

    public static function round(float|int|string|null $amount): float
    {
        return round((float) ($amount ?? 0), 2);
    }
}

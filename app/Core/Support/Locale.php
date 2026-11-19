<?php

namespace App\Core\Support;

final class Locale
{
    /**
     * @return array<string, array{label: string, dir: string, native: string}>
     */
    public static function all(): array
    {
        return config('sme.locales', []);
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::all());
    }

    public static function direction(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return self::all()[$locale]['dir'] ?? 'ltr';
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::direction($locale) === 'rtl';
    }
}

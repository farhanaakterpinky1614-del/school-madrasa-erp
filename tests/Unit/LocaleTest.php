<?php

use App\Core\Support\Locale;
use Tests\TestCase;

uses(TestCase::class);

it('lists supported locales', function () {
    expect(Locale::all())->toHaveKeys(['en', 'bn', 'ar']);
});

it('detects supported locales', function () {
    expect(Locale::isSupported('en'))->toBeTrue();
    expect(Locale::isSupported('xx'))->toBeFalse();
});

it('returns rtl only for arabic', function () {
    expect(Locale::direction('en'))->toBe('ltr');
    expect(Locale::direction('bn'))->toBe('ltr');
    expect(Locale::direction('ar'))->toBe('rtl');
});

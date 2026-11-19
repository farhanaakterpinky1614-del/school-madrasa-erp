<?php

use App\Core\Support\Locale;

it('redirects home to the dashboard', function () {
    $this->get('/')
        ->assertRedirect(route('dashboard'));
});

it('renders the dashboard shell', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Operations shell', false)
        ->assertSee('School &amp; Madrasa ERP', false);
});

it('renders the ui kitchen sink', function () {
    $this->get(route('ui.kitchen-sink'))
        ->assertOk()
        ->assertSee('Component kitchen sink', false);
});

it('switches locale to arabic and sets rtl direction helper', function () {
    $this->from(route('dashboard'))
        ->get(route('locale.update', 'ar'))
        ->assertRedirect(route('dashboard'));

    expect(session('locale'))->toBe('ar');
    expect(Locale::direction('ar'))->toBe('rtl');
    expect(Locale::isRtl('ar'))->toBeTrue();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('switches locale to bengali', function () {
    $this->from(route('dashboard'))
        ->get(route('locale.update', 'bn'))
        ->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="ltr"', false)
        ->assertSee('ড্যাশবোর্ড', false);
});

it('rejects unsupported locales', function () {
    $this->get(route('locale.update', 'xx'))
        ->assertNotFound();
});

<?php

use App\Core\Support\Locale;

it('redirects home to login when guest', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('renders the ui shell preview', function () {
    $this->get(route('ui.shell-preview'))
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
    $this->from(route('ui.shell-preview'))
        ->get(route('locale.update', 'ar'))
        ->assertRedirect(route('ui.shell-preview'));

    expect(session('locale'))->toBe('ar');
    expect(Locale::direction('ar'))->toBe('rtl');
    expect(Locale::isRtl('ar'))->toBeTrue();

    $this->get(route('ui.shell-preview'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('switches locale to bengali', function () {
    $this->from(route('ui.shell-preview'))
        ->get(route('locale.update', 'bn'))
        ->assertRedirect(route('ui.shell-preview'));

    $this->get(route('ui.shell-preview'))
        ->assertOk()
        ->assertSee('dir="ltr"', false)
        ->assertSee('ড্যাশবোর্ড', false);
});

it('rejects unsupported locales', function () {
    $this->get(route('locale.update', 'xx'))
        ->assertNotFound();
});

<?php

it('sends platform admin from /dashboard to the saas console instead of 403', function () {
    $this->actingAsPlatformAdmin();

    $this->get('/dashboard')->assertRedirect(route('platform.dashboard'));
});

it('redirects platform admin away from /app instead of forbidding', function () {
    $this->actingAsPlatformAdmin();

    $this->get('/app')->assertRedirect(route('platform.dashboard'));
});

it('lets platform admin open /platform with 200', function () {
    $this->actingAsPlatformAdmin();

    $this->get(route('platform.dashboard'))->assertOk();
});

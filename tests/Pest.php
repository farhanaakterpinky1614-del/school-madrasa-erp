<?php

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
    Tests\Support\Tenancy::class,
)->in('Feature');

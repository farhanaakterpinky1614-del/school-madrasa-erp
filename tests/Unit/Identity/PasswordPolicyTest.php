<?php

use Illuminate\Support\Facades\Validator;
use Modules\Identity\Services\PasswordPolicy;

uses(Tests\TestCase::class);

it('requires a number by default', function () {
    $rule = app(PasswordPolicy::class)->rule();

    $fail = Validator::make(
        ['password' => 'abcdefgh'],
        ['password' => [$rule]]
    );

    $pass = Validator::make(
        ['password' => 'abcdefg1'],
        ['password' => [$rule]]
    );

    expect($fail->fails())->toBeTrue()
        ->and($pass->fails())->toBeFalse();
});

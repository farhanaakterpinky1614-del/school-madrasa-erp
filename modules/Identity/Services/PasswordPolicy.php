<?php

namespace Modules\Identity\Services;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    public function rule(): Password
    {
        $cfg = config('identity.password');

        $rule = Password::min((int) $cfg['min_length']);

        if ($cfg['require_mixed_case']) {
            $rule->mixedCase();
        }

        if ($cfg['require_number']) {
            $rule->numbers();
        }

        if ($cfg['require_symbol']) {
            $rule->symbols();
        }

        return $rule;
    }
}

<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('sme.default_locale', 'en'));

        if (! Locale::isSupported($locale)) {
            $locale = config('sme.default_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}

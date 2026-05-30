<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale && $request->user()?->locale) {
            $locale = $request->user()->locale;
        }

        if (in_array($locale, ['en', 'ar'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

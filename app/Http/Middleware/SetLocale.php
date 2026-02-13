<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['es', 'en'];
        $locale = (string) $request->session()->get('locale', config('app.locale', 'es'));

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'es';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}

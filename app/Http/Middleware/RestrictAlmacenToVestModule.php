<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RestrictAlmacenToVestModule
{
    /**
     * @var list<string>
     */
    private const ALLOWED_ROUTE_PATTERNS = [
        'vests.*',
        'vest-imports.*',
        'profile.*',
        'password.force.edit',
        'password.force.update',
        'password.update',
        'logout',
        'locale.switch',
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isAlmacen()) {
            return $next($request);
        }

        foreach (self::ALLOWED_ROUTE_PATTERNS as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403);
        }

        return redirect()->route('vests.index');
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\TemporaryPhotoAccessGrant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRevistaGuestSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $grantId = $request->session()->get('revista_grant_id');

        if (! $grantId) {
            return redirect()->route('revista-armas.guest.login');
        }

        $grant = TemporaryPhotoAccessGrant::query()
            ->with('temporaryPhotoUser')
            ->find($grantId);

        if (! $grant || ! $grant->isCurrentlyValid() || ! $grant->temporaryPhotoUser?->is_active) {
            $request->session()->forget(['revista_grant_id', 'revista_temp_user_id']);

            return redirect()
                ->route('revista-armas.guest.login')
                ->withErrors(['access' => __('Su acceso temporal expiró o fue revocado.')]);
        }

        $request->attributes->set('revista_grant', $grant);

        return $next($request);
    }
}

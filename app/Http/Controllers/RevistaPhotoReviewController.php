<?php

namespace App\Http\Controllers;

use App\Models\TemporaryPhotoUser;
use App\Models\Weapon;
use App\Services\RevistaArmasScopeService;
use App\Services\WeaponPhotoStagingService;
use Illuminate\Http\Request;

class RevistaPhotoReviewController extends Controller
{
    public function __construct(
        private readonly WeaponPhotoStagingService $stagingService,
        private readonly RevistaArmasScopeService $scopeService,
    ) {
        $this->middleware(['auth', 'revista.staff']);
    }

    public function approve(Request $request, Weapon $weapon, TemporaryPhotoUser $temporaryPhotoUser)
    {
        $user = $request->user();
        $this->authorize('view', $temporaryPhotoUser);

        if (! $this->scopeService->canStaffManageWeapon($user, $weapon)) {
            abort(403);
        }

        $this->stagingService->approve($user, $temporaryPhotoUser, $weapon);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('Imágenes oficiales actualizadas.')]);
        }

        return back()->with('status', __('Imágenes oficiales actualizadas.'));
    }

    public function reject(Request $request, Weapon $weapon, TemporaryPhotoUser $temporaryPhotoUser)
    {
        $user = $request->user();
        $this->authorize('view', $temporaryPhotoUser);

        if (! $this->scopeService->canStaffManageWeapon($user, $weapon)) {
            abort(403);
        }

        $this->stagingService->reject($user, $temporaryPhotoUser, $weapon);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('Fotos en revisión eliminadas.')]);
        }

        return back()->with('status', __('Fotos en revisión eliminadas.'));
    }
}

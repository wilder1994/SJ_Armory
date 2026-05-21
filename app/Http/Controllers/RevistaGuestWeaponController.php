<?php

namespace App\Http\Controllers;

use App\Models\TemporaryPhotoAccessGrant;
use App\Models\Weapon;
use App\Services\TemporaryPhotoAccessService;
use App\Services\WeaponPhotoStagingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevistaGuestWeaponController extends Controller
{
    public function __construct(
        private readonly WeaponPhotoStagingService $stagingService,
        private readonly TemporaryPhotoAccessService $accessService,
    ) {
        $this->middleware('revista.guest');
    }

    public function index(Request $request): View
    {
        /** @var TemporaryPhotoAccessGrant $grant */
        $grant = $request->attributes->get('revista_grant');
        $temporaryUser = $grant->temporaryPhotoUser;

        $weaponIds = $this->accessService->grantWeaponIds($grant);

        $weapons = Weapon::query()
            ->with('activeClientAssignment.client')
            ->whereIn('id', $weaponIds)
            ->orderBy('serial_number')
            ->get();

        $rows = $weapons->map(fn (Weapon $weapon) => [
            'weapon' => $weapon,
            'is_complete' => $this->stagingService->isStagingComplete($temporaryUser, $weapon),
        ]);

        return view('revista-armas.guest.weapons', [
            'rows' => $rows,
            'temporaryUser' => $temporaryUser,
            'expiresAt' => $grant->expires_at,
        ]);
    }

    public function stagingState(Request $request, Weapon $weapon)
    {
        /** @var TemporaryPhotoAccessGrant $grant */
        $grant = $request->attributes->get('revista_grant');
        $temporaryUser = $grant->temporaryPhotoUser;

        $allowedWeaponIds = $this->accessService->grantWeaponIds($grant);
        if (! $allowedWeaponIds->contains($weapon->id)) {
            abort(403);
        }

        $staging = $this->stagingService->stagingByDescription($temporaryUser, $weapon);

        return response()->json([
            'slots' => collect(\App\Support\RevistaWeaponPhotoSlots::DESCRIPTIONS)->map(function ($label, $description) use ($staging) {
                $row = $staging[$description] ?? null;
                $url = $row?->file
                    ? \Illuminate\Support\Facades\Storage::disk($row->file->disk)->url($row->file->path)
                    : null;

                return [
                    'description' => $description,
                    'label' => $label,
                    'url' => $url,
                ];
            })->values(),
            'is_complete' => $this->stagingService->isStagingComplete($temporaryUser, $weapon),
        ]);
    }
}

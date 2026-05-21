<?php

namespace App\Http\Controllers;

use App\Models\TemporaryPhotoUser;
use App\Models\Weapon;
use App\Services\RevistaArmasScopeService;
use App\Services\WeaponPhotoStagingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevistaArmasController extends Controller
{
    public function __construct(
        private readonly RevistaArmasScopeService $scopeService,
        private readonly WeaponPhotoStagingService $stagingService,
    ) {
        $this->middleware(['auth', 'revista.staff']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $temporaryPhotoUserId = $request->integer('temporary_photo_user_id') ?: null;

        $temporaryUsers = $this->scopeService->temporaryUsersQueryForStaff($user)
            ->active()
            ->orderBy('name')
            ->get();

        $weapons = $this->scopeService->weaponsQueryForStaff($user)->get();

        $rows = $weapons->map(function (Weapon $weapon) use ($temporaryPhotoUserId, $temporaryUsers) {
            $completions = [];

            if ($temporaryPhotoUserId) {
                $tempUser = $temporaryUsers->firstWhere('id', $temporaryPhotoUserId);
                if ($tempUser) {
                    $completions[$temporaryPhotoUserId] = $this->stagingService->isStagingComplete($tempUser, $weapon);
                }
            } else {
                foreach ($temporaryUsers as $tempUser) {
                    $completions[$tempUser->id] = $this->stagingService->isStagingComplete($tempUser, $weapon);
                }
            }

            return [
                'weapon' => $weapon,
                'completions' => $completions,
            ];
        });

        return view('revista-armas.index', [
            'rows' => $rows,
            'temporaryUsers' => $temporaryUsers,
            'selectedTemporaryUserId' => $temporaryPhotoUserId,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function review(Request $request, Weapon $weapon, TemporaryPhotoUser $temporaryPhotoUser)
    {
        $user = $request->user();
        $this->authorize('view', $temporaryPhotoUser);

        if (! $this->scopeService->canStaffManageWeapon($user, $weapon)) {
            abort(403);
        }

        $staging = $this->stagingService->stagingByDescription($temporaryPhotoUser, $weapon);

        return response()->json([
            'weapon' => [
                'id' => $weapon->id,
                'serial_number' => $weapon->serial_number,
            ],
            'temporary_user' => [
                'id' => $temporaryPhotoUser->id,
                'name' => $temporaryPhotoUser->name,
            ],
            'slots' => collect($staging)->map(function ($row, $description) {
                $url = $row->file
                    ? \Illuminate\Support\Facades\Storage::disk($row->file->disk)->url($row->file->path)
                    : null;

                return [
                    'description' => $description,
                    'label' => \App\Support\RevistaWeaponPhotoSlots::DESCRIPTIONS[$description] ?? $description,
                    'url' => $url,
                ];
            })->values(),
            'is_complete' => $this->stagingService->isStagingComplete($temporaryPhotoUser, $weapon),
        ]);
    }
}

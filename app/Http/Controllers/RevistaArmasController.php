<?php

namespace App\Http\Controllers;

use App\Models\TemporaryPhotoUser;
use App\Models\Weapon;
use App\Support\RevistaWeaponPhotoSlots;
use App\Services\RevistaArmasScopeService;
use App\Services\TemporaryPhotoAccessService;
use App\Services\WeaponPhotoStagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RevistaArmasController extends Controller
{
    private const INDEX_PER_PAGE = 50;

    private const ASSIGN_SEARCH_LIMIT = 60;

    public function __construct(
        private readonly RevistaArmasScopeService $scopeService,
        private readonly WeaponPhotoStagingService $stagingService,
        private readonly TemporaryPhotoAccessService $accessService,
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

        $activeGrantMissing = false;
        $noGrantHistory = false;
        $needsTemporaryUserSelection = $temporaryPhotoUserId === null;
        $weapons = null;
        $rows = collect();

        if ($temporaryPhotoUserId !== null) {
            $temporaryUser = $temporaryUsers->firstWhere('id', $temporaryPhotoUserId);

            if ($temporaryUser) {
                $activeGrantMissing = $this->accessService->activeGrantFor($temporaryUser) === null;
                $grantForList = $this->accessService->latestGrantFor($temporaryUser);

                $weaponsQuery = $this->scopeService->weaponsQueryForStaff($user);

                if ($grantForList) {
                    $weaponIds = $this->accessService->grantWeaponIds($grantForList);
                    $weaponsQuery->whereIn('weapons.id', $weaponIds);
                } else {
                    $noGrantHistory = true;
                    $weaponsQuery->whereRaw('0 = 1');
                }

                $weapons = $weaponsQuery
                    ->paginate(self::INDEX_PER_PAGE)
                    ->withQueryString();

                $pageWeaponIds = $weapons->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
                $completedIds = array_fill_keys(
                    $this->stagingService->completedWeaponIdsForTemporaryUser($temporaryPhotoUserId, $pageWeaponIds),
                    true
                );

                $rows = $weapons->getCollection()->map(function (Weapon $weapon) use ($temporaryPhotoUserId, $completedIds) {
                    return [
                        'weapon' => $weapon,
                        'completions' => [
                            $temporaryPhotoUserId => isset($completedIds[$weapon->id]),
                        ],
                    ];
                });
            } else {
                $needsTemporaryUserSelection = true;
            }
        }

        return view('revista-armas.index', [
            'rows' => $rows,
            'weapons' => $weapons,
            'temporaryUsers' => $temporaryUsers,
            'selectedTemporaryUserId' => $temporaryPhotoUserId,
            'activeGrantMissing' => $activeGrantMissing,
            'noGrantHistory' => $noGrantHistory,
            'needsTemporaryUserSelection' => $needsTemporaryUserSelection,
            'isAdmin' => $user->isAdmin(),
            'assignWeaponsSearchUrl' => route('revista-armas.weapons.search'),
            'assignmentContextUrlTemplate' => route('revista-armas.access.context', ['temporary_photo_user' => '__ID__']),
            'accessRenewUrl' => route('revista-armas.access.renew'),
        ]);
    }

    public function searchAssignableWeapons(Request $request): JsonResponse
    {
        $user = $request->user();
        $term = trim($request->string('q')->toString());
        $limit = min(100, max(1, $request->integer('limit') ?: self::ASSIGN_SEARCH_LIMIT));
        $temporaryPhotoUserId = $request->integer('temporary_photo_user_id') ?: null;

        $query = $this->scopeService->weaponsQueryForStaff($user, [
            'activeClientAssignment.client',
            'activePendingTransfer.fromClient',
        ]);

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($inner) use ($like) {
                $inner
                    ->where('serial_number', 'like', $like)
                    ->orWhere('internal_code', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('caliber', 'like', $like)
                    ->orWhere('weapon_type', 'like', $like)
                    ->orWhere('permit_number', 'like', $like)
                    ->orWhereHas('activeClientAssignment.client', function ($clientQuery) use ($like) {
                        $clientQuery->where('name', 'like', $like);
                    })
                    ->orWhereHas('activePendingTransfer.fromClient', function ($clientQuery) use ($like) {
                        $clientQuery->where('name', 'like', $like);
                    });
            });
        }

        /** @var Collection<int, Weapon> $weapons */
        $weapons = $query->limit($limit)->get();
        $weaponIds = $weapons->pluck('id')->map(fn ($id) => (int) $id)->all();

        $lockedIds = [];
        $foreignBlockedIds = [];
        if ($temporaryPhotoUserId) {
            $lockedIds = array_fill_keys(
                $this->stagingService->weaponIdsWithAnyStagingForTemporaryUser($temporaryPhotoUserId),
                true
            );
            foreach ($this->stagingService->foreignStagingConflicts($weaponIds, $temporaryPhotoUserId) as $conflict) {
                $foreignBlockedIds[(int) $conflict['weapon_id']] = $conflict['temporary_user_name'];
            }
        }

        return response()->json([
            'items' => $weapons->map(function (Weapon $weapon) use ($lockedIds, $foreignBlockedIds) {
                $clientName = $weapon->operationalDisplayClient()?->name;
                $hasOwnStaging = isset($lockedIds[$weapon->id]);
                $blockedBy = $foreignBlockedIds[$weapon->id] ?? null;

                return [
                    'id' => $weapon->id,
                    'serial_number' => $weapon->serial_number,
                    'weapon_type' => $weapon->weapon_type,
                    'client_name' => $clientName ?: '—',
                    'has_staging' => $hasOwnStaging,
                    'locked' => $hasOwnStaging,
                    'blocked_by_other' => $blockedBy,
                    'search' => mb_strtolower(implode(' ', array_filter([
                        $weapon->internal_code,
                        $weapon->serial_number,
                        $weapon->weapon_type,
                        $weapon->brand,
                        $weapon->caliber,
                        $clientName,
                    ], fn ($value) => filled($value))), 'UTF-8'),
                ];
            })->values()->all(),
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
        $requiredCount = RevistaWeaponPhotoSlots::requiredCount();
        $uploadedCount = collect($staging)->filter(fn ($row) => $row->file !== null)->count();
        $pendingCount = max(0, $requiredCount - $uploadedCount);

        return response()->json([
            'weapon' => [
                'id' => $weapon->id,
                'serial_number' => $weapon->serial_number,
            ],
            'temporary_user' => [
                'id' => $temporaryPhotoUser->id,
                'name' => $temporaryPhotoUser->name,
            ],
            'slots' => collect(RevistaWeaponPhotoSlots::DESCRIPTIONS)->map(function ($label, $description) use ($staging) {
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
            'required_count' => $requiredCount,
            'uploaded_count' => $uploadedCount,
            'pending_count' => $pendingCount,
            'is_complete' => $pendingCount === 0,
        ]);
    }
}

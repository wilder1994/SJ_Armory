<?php

namespace App\Services;

use App\Mail\RevistaTemporaryAccessMail;
use App\Models\TemporaryPhotoAccessGrant;
use App\Models\TemporaryPhotoAccessWeapon;
use App\Models\TemporaryPhotoUser;
use App\Models\User;
use App\Models\Weapon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TemporaryPhotoAccessService
{
    public function __construct(
        private readonly RevistaArmasScopeService $scopeService,
        private readonly WeaponPhotoStagingService $stagingService,
    ) {
    }

    /**
     * @param  array<int, int>  $weaponIds
     * @return array{grant: TemporaryPhotoAccessGrant, plain_code: ?string, appended: bool, renewed: bool}
     */
    public function createGrant(User $actor, TemporaryPhotoUser $temporaryUser, array $weaponIds): array
    {
        $this->ensureCanManageTemporaryUser($actor, $temporaryUser);

        $weaponIds = collect($weaponIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($weaponIds->isEmpty()) {
            throw new RuntimeException(__('Debe seleccionar al menos un arma.'));
        }

        $this->assertWeaponsInActorScope($actor, $weaponIds);
        $this->assertNoForeignStagingConflicts($weaponIds->all(), (int) $temporaryUser->id);

        if ($temporaryUser->is_shared) {
            $activeGrant = $this->activeGrantFor($temporaryUser);
            if ($activeGrant) {
                return $this->appendWeaponsToGrant($actor, $temporaryUser, $activeGrant, $weaponIds);
            }
        }

        $this->assertStagingWeaponsIncluded($temporaryUser, $weaponIds);

        return $this->issueNewGrant($actor, $temporaryUser, $weaponIds, renewed: false);
    }

    /**
     * Renew the latest grant with the same weapons and a new access code.
     *
     * @return array{grant: TemporaryPhotoAccessGrant, plain_code: string, appended: bool, renewed: bool}
     */
    public function renewLatestGrant(User $actor, TemporaryPhotoUser $temporaryUser): array
    {
        $this->ensureCanManageTemporaryUser($actor, $temporaryUser);

        $latest = $this->latestGrantFor($temporaryUser);
        if (! $latest) {
            throw new RuntimeException(__('Este usuario temporal no tiene un acceso previo para renovar.'));
        }

        $weaponIds = $this->grantWeaponIds($latest)->map(fn ($id) => (int) $id)->unique()->values();
        if ($weaponIds->isEmpty()) {
            throw new RuntimeException(__('El último acceso no tiene armas asignadas.'));
        }

        $this->assertWeaponsInActorScope($actor, $weaponIds);
        $this->assertNoForeignStagingConflicts($weaponIds->all(), (int) $temporaryUser->id);

        return $this->issueNewGrant($actor, $temporaryUser, $weaponIds, renewed: true);
    }

    public function revokeGrant(User $actor, TemporaryPhotoAccessGrant $grant): void
    {
        $grant->loadMissing('temporaryPhotoUser');
        $this->ensureCanManageTemporaryUser($actor, $grant->temporaryPhotoUser);

        if ($grant->revoked_at !== null) {
            return;
        }

        $grant->update(['revoked_at' => now()]);
    }

    public function validateGuestLogin(string $email, string $code): ?TemporaryPhotoAccessGrant
    {
        $temporaryUser = TemporaryPhotoUser::query()
            ->active()
            ->where('email', mb_strtolower(trim($email)))
            ->first();

        if (! $temporaryUser) {
            return null;
        }

        $grant = TemporaryPhotoAccessGrant::query()
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $grant || ! Hash::check($code, $grant->access_code_hash)) {
            return null;
        }

        return $grant->load(['temporaryPhotoUser.ownerResponsible', 'weapons']);
    }

    public function grantWeaponIds(TemporaryPhotoAccessGrant $grant): Collection
    {
        return $grant->weapons()->pluck('weapon_id');
    }

    public function activeGrantFor(TemporaryPhotoUser $temporaryUser): ?TemporaryPhotoAccessGrant
    {
        return TemporaryPhotoAccessGrant::query()
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function latestGrantFor(TemporaryPhotoUser $temporaryUser): ?TemporaryPhotoAccessGrant
    {
        return TemporaryPhotoAccessGrant::query()
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->latest('id')
            ->first();
    }

    /**
     * Context for the assign modal: latest weapons + staging locks.
     *
     * @return array{
     *     can_renew: bool,
     *     latest_weapon_ids: list<int>,
     *     staging_weapons: list<array{id: int, serial_number: string|null, photo_count: int, is_complete: bool}>,
     *     locked_weapon_ids: list<int>
     * }
     */
    public function assignmentContext(TemporaryPhotoUser $temporaryUser): array
    {
        $latest = $this->latestGrantFor($temporaryUser);
        $latestWeaponIds = $latest
            ? $this->grantWeaponIds($latest)->map(fn ($id) => (int) $id)->values()->all()
            : [];

        $stagingWeaponIds = $this->stagingService->weaponIdsWithAnyStagingForTemporaryUser((int) $temporaryUser->id);
        $counts = $this->stagingService->stagingCountsByWeaponForTemporaryUser((int) $temporaryUser->id, $stagingWeaponIds);
        $required = \App\Support\RevistaWeaponPhotoSlots::requiredCount();

        $weapons = $stagingWeaponIds === []
            ? collect()
            : Weapon::query()->whereIn('id', $stagingWeaponIds)->get(['id', 'serial_number'])->keyBy('id');

        $stagingWeapons = collect($stagingWeaponIds)->map(function (int $weaponId) use ($weapons, $counts, $required) {
            $photoCount = $counts[$weaponId] ?? 0;

            return [
                'id' => $weaponId,
                'serial_number' => $weapons->get($weaponId)?->serial_number,
                'photo_count' => $photoCount,
                'is_complete' => $photoCount >= $required,
            ];
        })->values()->all();

        return [
            'can_renew' => $latest !== null && $latestWeaponIds !== [],
            'latest_weapon_ids' => $latestWeaponIds,
            'staging_weapons' => $stagingWeapons,
            'locked_weapon_ids' => $stagingWeaponIds,
        ];
    }

    /**
     * @param  Collection<int, int>  $weaponIds
     * @return array{grant: TemporaryPhotoAccessGrant, plain_code: ?string, appended: bool, renewed: bool}
     */
    private function appendWeaponsToGrant(
        User $actor,
        TemporaryPhotoUser $temporaryUser,
        TemporaryPhotoAccessGrant $grant,
        Collection $weaponIds,
    ): array {
        $this->assertNoForeignStagingConflicts($weaponIds->all(), (int) $temporaryUser->id);

        $existingWeaponIds = $grant->weapons()->pluck('weapon_id')->map(fn ($id) => (int) $id);

        DB::transaction(function () use ($grant, $weaponIds, $existingWeaponIds) {
            foreach ($weaponIds as $weaponId) {
                if ($existingWeaponIds->contains($weaponId)) {
                    continue;
                }

                TemporaryPhotoAccessWeapon::create([
                    'temporary_photo_access_grant_id' => $grant->id,
                    'weapon_id' => $weaponId,
                ]);
            }

            $grant->update([
                'expires_at' => now()->addHours(12),
            ]);
        });

        return [
            'grant' => $grant->fresh()->load('weapons.weapon'),
            'plain_code' => null,
            'appended' => true,
            'renewed' => false,
        ];
    }

    /**
     * @param  Collection<int, int>  $weaponIds
     * @return array{grant: TemporaryPhotoAccessGrant, plain_code: string, appended: bool, renewed: bool}
     */
    private function issueNewGrant(
        User $actor,
        TemporaryPhotoUser $temporaryUser,
        Collection $weaponIds,
        bool $renewed,
    ): array {
        $plainCode = $this->generatePlainCode();

        $grant = DB::transaction(function () use ($actor, $temporaryUser, $weaponIds, $plainCode) {
            $this->revokeActiveGrants($temporaryUser);

            $grant = TemporaryPhotoAccessGrant::create([
                'temporary_photo_user_id' => $temporaryUser->id,
                'created_by_user_id' => $actor->id,
                'access_code_hash' => Hash::make($plainCode),
                'expires_at' => now()->addHours(12),
            ]);

            foreach ($weaponIds as $weaponId) {
                TemporaryPhotoAccessWeapon::create([
                    'temporary_photo_access_grant_id' => $grant->id,
                    'weapon_id' => $weaponId,
                ]);
            }

            return $grant;
        });

        $this->sendAccessEmail($temporaryUser, $grant, $plainCode);

        return [
            'grant' => $grant->load('weapons.weapon'),
            'plain_code' => $plainCode,
            'appended' => false,
            'renewed' => $renewed,
        ];
    }

    /**
     * @param  Collection<int, int>  $weaponIds
     */
    private function assertWeaponsInActorScope(User $actor, Collection $weaponIds): void
    {
        $weapons = $this->scopeService->weaponsQueryForStaff($actor)
            ->whereIn('id', $weaponIds)
            ->get();

        if ($weapons->count() !== $weaponIds->count()) {
            throw new RuntimeException(__('Una o más armas no están disponibles para su cartera.'));
        }

        foreach ($weapons as $weapon) {
            if (! $this->scopeService->canStaffManageWeapon($actor, $weapon)) {
                throw new RuntimeException(__('No tiene permiso sobre todas las armas seleccionadas.'));
            }
        }
    }

    /**
     * @param  Collection<int, int>  $weaponIds
     */
    private function assertStagingWeaponsIncluded(TemporaryPhotoUser $temporaryUser, Collection $weaponIds): void
    {
        $stagingWeaponIds = collect(
            $this->stagingService->weaponIdsWithAnyStagingForTemporaryUser((int) $temporaryUser->id)
        );

        $omitted = $stagingWeaponIds->diff($weaponIds)->values();
        if ($omitted->isEmpty()) {
            return;
        }

        $serials = Weapon::query()
            ->whereIn('id', $omitted->all())
            ->orderBy('serial_number')
            ->pluck('serial_number')
            ->filter()
            ->values()
            ->all();

        $list = $serials !== [] ? implode(', ', $serials) : $omitted->implode(', ');

        throw ValidationException::withMessages([
            'weapon_ids' => [
                __('No puede dejar fuera armas con fotos pendientes (:weapons). Actualice o elimine esas fotos antes de continuar.', [
                    'weapons' => $list,
                ]),
            ],
        ]);
    }

    /**
     * @param  list<int>  $weaponIds
     */
    private function assertNoForeignStagingConflicts(array $weaponIds, int $temporaryUserId): void
    {
        $conflicts = $this->stagingService->foreignStagingConflicts($weaponIds, $temporaryUserId);
        if ($conflicts === []) {
            return;
        }

        $parts = collect($conflicts)
            ->map(fn (array $row) => ($row['serial_number'] ?? ('#'.$row['weapon_id'])).' ('.$row['temporary_user_name'].')')
            ->unique()
            ->values()
            ->all();

        throw ValidationException::withMessages([
            'weapon_ids' => [
                __('No puede asignar armas con fotos pendientes de otro usuario temporal: :items. Actualice o elimine esas fotos primero.', [
                    'items' => implode(', ', $parts),
                ]),
            ],
        ]);
    }

    private function revokeActiveGrants(TemporaryPhotoUser $temporaryUser): void
    {
        TemporaryPhotoAccessGrant::query()
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->update(['revoked_at' => now()]);
    }

    private function generatePlainCode(): string
    {
        return Str::upper(Str::random(8));
    }

    private function sendAccessEmail(TemporaryPhotoUser $temporaryUser, TemporaryPhotoAccessGrant $grant, string $plainCode): void
    {
        try {
            Mail::to($temporaryUser->email)->send(new RevistaTemporaryAccessMail(
                recipientName: $temporaryUser->name,
                loginUrl: route('revista-armas.guest.login'),
                loginEmail: $temporaryUser->email,
                accessCode: $plainCode,
                expiresAt: $grant->expires_at,
                appName: (string) config('app.name'),
            ));
        } catch (\Throwable) {
            // El responsable puede copiar credenciales desde el modal de éxito.
        }
    }

    public function ensureCanManageTemporaryUser(User $actor, TemporaryPhotoUser $temporaryUser): void
    {
        if ($temporaryUser->canBeManagedBy($actor)) {
            return;
        }

        abort(403);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\RetireWeaponAssignmentRequest;
use App\Http\Requests\StoreWeaponAssignmentRequest;
use App\Models\Weapon;
use App\Services\WeaponAssignmentService;

class WeaponClientAssignmentController extends Controller
{
    private const BLOCKED_STATUSES = [
        'in_maintenance',
        'seized_or_withdrawn',
        'decommissioned',
    ];

    public function store(StoreWeaponAssignmentRequest $request, Weapon $weapon, WeaponAssignmentService $service)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $data = $request->validated();

        $this->authorizeAssignment($weapon, $user, $data['client_id']);

        $startAt = $data['start_at'] ?? now();
        $startAtValue = $startAt instanceof \DateTimeInterface ? $startAt->format('Y-m-d H:i:s') : (string)$startAt;

        $active = $weapon->clientAssignments()->where('is_active', true)->first();
        $level = $user->responsibilityLevel?->level;

        if ($active) {
            if (!$user->isAdmin() && $level < 3) {
                abort(403, 'Nivel insuficiente para reasignar.');
            }
        } else {
            if (!$user->isAdmin() && $level < 2) {
                abort(403, 'Nivel insuficiente para asignar.');
            }
        }

        $service->assignClient($weapon, (int)$data['client_id'], $user, $startAtValue, $data['reason'] ?? null);

        return redirect()->route('weapons.index')->with('status', 'Destino actualizado.');
    }

    public function retire(RetireWeaponAssignmentRequest $request, Weapon $weapon, WeaponAssignmentService $service)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $level = $user->responsibilityLevel?->level;
        if (!$user->isAdmin() && $level < 3) {
            abort(403, 'Nivel insuficiente para retirar destino.');
        }

        $active = $weapon->clientAssignments()->where('is_active', true)->first();
        if (!$active) {
            return redirect()->back()->with('status', 'El arma no tiene destino activo.');
        }

        $service->retireAssignment($weapon, $user);

        return redirect()->route('weapons.index')->with('status', 'Destino retirado.');
    }

    private function authorizeAssignment(Weapon $weapon, $user, int $clientId): void
    {
        if ($weapon->operational_status && in_array($weapon->operational_status, self::BLOCKED_STATUSES, true)) {
            abort(422, 'El estado operativo impide asignar destino.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if (!$user->isResponsible()) {
            abort(403);
        }

        $activeCustody = $weapon->activeCustody;
        if (!$activeCustody || $activeCustody->custodian_user_id !== $user->id) {
            abort(403, 'El arma no esta bajo su custodia.');
        }

        $inPortfolio = $user->clients()->whereKey($clientId)->exists();
        if (!$inPortfolio) {
            abort(403, 'El cliente no pertenece a su cartera.');
        }
    }
}

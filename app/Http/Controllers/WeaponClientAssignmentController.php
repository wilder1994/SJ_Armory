<?php

namespace App\Http\Controllers;

use App\Http\Requests\RetireWeaponAssignmentRequest;
use App\Http\Requests\StoreWeaponAssignmentRequest;
use App\Models\User;
use App\Models\Weapon;
use App\Services\WeaponAssignmentService;

class WeaponClientAssignmentController extends Controller
{
    public function store(StoreWeaponAssignmentRequest $request, Weapon $weapon, WeaponAssignmentService $service)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        if (!$user->isAdmin()) {
            abort(403);
        }

        $data = $request->validated();
        $responsibleUserId = (int)($data['responsible_user_id'] ?? 0);

        if (!$user->isAdmin() && $user->isResponsible()) {
            $responsibleUserId = $user->id;
        }

        $responsible = User::where('role', 'RESPONSABLE')->find($responsibleUserId);
        if (!$responsible) {
            abort(422, 'El responsable seleccionado no es valido.');
        }

        $this->authorizeAssignment($weapon, $user, (int)$data['client_id'], $responsibleUserId);

        $startAt = $data['start_at'] ?? now();
        $startAtValue = $startAt instanceof \DateTimeInterface ? $startAt->format('Y-m-d') : (string)$startAt;

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

        $service->assignClient(
            $weapon,
            (int)$data['client_id'],
            $responsible,
            $user,
            $startAtValue,
            $data['reason'] ?? null
        );

        return redirect()->route('weapons.index')->with('status', 'Destino actualizado.');
    }

    public function retire(RetireWeaponAssignmentRequest $request, Weapon $weapon, WeaponAssignmentService $service)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        if (!$user->isAdmin()) {
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

    private function authorizeAssignment(Weapon $weapon, $user, int $clientId, int $responsibleUserId): void
    {
        if (!$user->isAdmin()) {
            abort(403);
        }
    }
}


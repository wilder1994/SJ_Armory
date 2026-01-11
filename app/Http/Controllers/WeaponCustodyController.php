<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeaponCustodyRequest;
use App\Models\User;
use App\Models\Weapon;
use App\Services\WeaponCustodyService;

class WeaponCustodyController extends Controller
{
    public function store(StoreWeaponCustodyRequest $request, Weapon $weapon, WeaponCustodyService $service)
    {
        $user = $request->user();
        $data = $request->validated();

        $custodian = User::where('role', 'RESPONSABLE')->findOrFail($data['custodian_user_id']);
        $startAt = $data['start_at'] ?? now();
        $startAtValue = $startAt instanceof \DateTimeInterface ? $startAt->format('Y-m-d H:i:s') : (string)$startAt;

        $service->assignCustody($weapon, $custodian, $user, $startAtValue, $data['reason'] ?? null);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Custodia actualizada.');
    }
}

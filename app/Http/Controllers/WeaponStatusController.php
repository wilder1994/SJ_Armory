<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWeaponStatusRequest;
use App\Models\Weapon;

class WeaponStatusController extends Controller
{
    public function update(UpdateWeaponStatusRequest $request, Weapon $weapon)
    {
        $weapon->update([
            'operational_status' => $request->string('operational_status')->toString(),
        ]);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Estado operativo actualizado.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use Illuminate\Database\Seeder;

class WeaponClientAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::orderBy('id')->get();
        $responsibles = User::where('role', 'RESPONSABLE')->orderBy('id')->get();
        $admin = User::where('role', 'ADMIN')->orderBy('id')->first();

        if ($clients->isEmpty() || $responsibles->isEmpty()) {
            return;
        }

        $weapons = Weapon::orderBy('id')->get();
        if ($weapons->isEmpty()) {
            return;
        }

        $responsibleIndex = 0;
        $clientIndex = 0;

        foreach ($weapons as $weapon) {
            $existing = $weapon->clientAssignments()->where('is_active', true)->first();
            if ($existing) {
                continue;
            }

            $client = $clients[$clientIndex % $clients->count()];
            $responsible = $responsibles[$responsibleIndex % $responsibles->count()];

            WeaponClientAssignment::create([
                'weapon_id' => $weapon->id,
                'client_id' => $client->id,
                'responsible_user_id' => $responsible->id,
                'start_at' => now()->toDateString(),
                'is_active' => true,
                'assigned_by' => $admin?->id ?? $responsible->id,
                'reason' => 'Asignación inicial de prueba',
            ]);

            $clientIndex++;
            $responsibleIndex++;
        }
    }
}


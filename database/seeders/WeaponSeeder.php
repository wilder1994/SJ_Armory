<?php

namespace Database\Seeders;

use App\Models\Weapon;
use Illuminate\Database\Seeder;

class WeaponSeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            ['weapon_type' => 'Pistola', 'brand' => 'Cordova', 'caliber' => '9 mm', 'capacity' => '15', 'permit_type' => 'porte', 'count' => 5],
            ['weapon_type' => 'Revolver', 'brand' => '38L', 'caliber' => '38L', 'capacity' => '6', 'permit_type' => 'tenencia', 'count' => 5],
            ['weapon_type' => 'Pistola', 'brand' => 'Stoger', 'caliber' => '9 mm', 'capacity' => '15', 'permit_type' => 'porte', 'count' => 5],
            ['weapon_type' => 'Pistola', 'brand' => 'CZ', 'caliber' => '9 mm', 'capacity' => '15', 'permit_type' => 'porte', 'count' => 5],
        ];

        $i = 1;
        foreach ($series as $item) {
            for ($j = 1; $j <= $item['count']; $j++) {
                Weapon::create([
                    'internal_code' => sprintf('SJ-%04d', $i),
                    'serial_number' => sprintf('SN-2026-%04d', $i),
                    'weapon_type' => $item['weapon_type'],
                    'caliber' => $item['caliber'],
                    'brand' => $item['brand'],
                    'capacity' => $item['capacity'],
                    'ownership_type' => 'company_owned',
                    'ownership_entity' => 'SJ Seguridad LTDA',
                    'permit_type' => $item['permit_type'],
                    'permit_number' => sprintf('PER-%04d', $i),
                    'permit_expires_at' => now()->addMonths(24)->toDateString(),
                    'notes' => 'Seed de armas',
                ]);
                $i++;
            }
        }
    }
}

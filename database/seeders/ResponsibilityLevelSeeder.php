<?php

namespace Database\Seeders;

use App\Models\ResponsibilityLevel;
use Illuminate\Database\Seeder;

class ResponsibilityLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level' => 1, 'name' => 'Nivel 1', 'description' => 'Solo ver'],
            ['level' => 2, 'name' => 'Nivel 2', 'description' => 'Asignar a cliente sin destino activo'],
            ['level' => 3, 'name' => 'Nivel 3', 'description' => 'Reasignar y retirar destino'],
            ['level' => 4, 'name' => 'Nivel 4', 'description' => 'Igual que nivel 3'],
        ];

        foreach ($levels as $level) {
            ResponsibilityLevel::updateOrCreate(
                ['level' => $level['level']],
                $level
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Administrador', 'description' => 'Gestion general del sistema'],
            ['name' => 'Responsable', 'description' => 'Gestion de asignaciones'],
            ['name' => 'Auditor', 'description' => 'Lectura y revision'],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['name' => $position['name']],
                $position
            );
        }
    }
}

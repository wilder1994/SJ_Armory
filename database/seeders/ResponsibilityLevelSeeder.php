<?php

namespace Database\Seeders;

use App\Models\ResponsibilityLevel;
use Illuminate\Database\Seeder;

class ResponsibilityLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'level' => 1,
                'name' => 'Asignado con gestión',
                'description' => 'Acceso al recurso asignado con permisos de gestión.',
            ],
            [
                'level' => 2,
                'name' => 'Asignado solo lectura',
                'description' => 'Acceso al recurso asignado solo en modo lectura (buscar y filtrar).',
            ],
        ];

        foreach ($levels as $level) {
            ResponsibilityLevel::updateOrCreate(
                ['level' => $level['level']],
                [
                    'name' => $level['name'],
                    'description' => $level['description'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Analista de Operaciones', 'description' => 'Analiza procesos operativos y soporte de decisiones.'],
            ['name' => 'Coordinador de Operaciones', 'description' => 'Coordina operaciones y seguimiento de equipos.'],
            ['name' => 'Director de Gestion del Riesgo', 'description' => 'Define controles y politicas de gestion del riesgo.'],
            ['name' => 'Gerencia General', 'description' => 'Direccion estrategica y supervision integral.'],
            ['name' => 'Jefe de Operaciones', 'description' => 'Lidera la operacion diaria y cumplimiento operativo.'],
            ['name' => 'Supervisor', 'description' => 'Supervisa operaciones y asignaciones.'],
        ];

        $validNames = array_column($positions, 'name');

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['name' => $position['name']],
                ['description' => $position['description']]
            );
        }

        $fallbackPositionId = Position::where('name', 'Gerencia General')->value('id');
        $validIds = Position::whereIn('name', $validNames)->pluck('id');

        DB::table('users')
            ->whereNotNull('position_id')
            ->whereNotIn('position_id', $validIds)
            ->update(['position_id' => $fallbackPositionId]);

        Position::whereNotIn('name', $validNames)->delete();
    }
}

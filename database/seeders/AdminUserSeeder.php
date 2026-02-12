<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\ResponsibilityLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $positions = Position::pluck('id', 'name');
        $levels = ResponsibilityLevel::pluck('id', 'level');

        $admins = [
            [
                'name' => 'Wilder Rivera',
                'email' => 'wilder.rivera@example.com',
                'password' => 'WilderA9K3',
                'role' => 'ADMIN',
                'position' => 'Administrador',
                'responsibility_level' => 4,
                'is_active' => true,
                'cost_center' => 'CC-1001',
            ],
            [
                'name' => 'Andres San Miguel',
                'email' => 'andres.sanmiguel@example.com',
                'password' => 'AndresM7P2',
                'role' => 'ADMIN',
                'position' => 'Administrador',
                'responsibility_level' => 4,
                'is_active' => true,
                'cost_center' => 'CC-1002',
            ],
            [
                'name' => 'Camila Herrera',
                'email' => 'camila.herrera@example.com',
                'password' => 'CamilaR4T7',
                'role' => 'RESPONSABLE',
                'position' => 'Responsable',
                'responsibility_level' => 3,
                'is_active' => true,
                'cost_center' => 'CC-2001',
            ],
            [
                'name' => 'Julian Torres',
                'email' => 'julian.torres@example.com',
                'password' => 'JulianT6P9',
                'role' => 'RESPONSABLE',
                'position' => 'Responsable',
                'responsibility_level' => 2,
                'is_active' => true,
                'cost_center' => 'CC-2002',
            ],
            [
                'name' => 'Paula Rojas',
                'email' => 'paula.rojas@example.com',
                'password' => 'PaulaR8K5',
                'role' => 'RESPONSABLE',
                'position' => 'Responsable',
                'responsibility_level' => 2,
                'is_active' => true,
                'cost_center' => 'CC-2003',
            ],
            [
                'name' => 'Mateo Garcia',
                'email' => 'mateo.garcia@example.com',
                'password' => 'MateoG3L1',
                'role' => 'AUDITOR',
                'position' => 'Auditor',
                'responsibility_level' => 1,
                'is_active' => true,
                'cost_center' => 'CC-3001',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'role' => $admin['role'],
                    'position_id' => $positions[$admin['position']] ?? null,
                    'responsibility_level_id' => $levels[$admin['responsibility_level']] ?? null,
                    'is_active' => $admin['is_active'],
                    'cost_center' => $admin['cost_center'],
                ]
            );
        }
    }
}


<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::orderBy('id')->get();
        $responsibles = User::where('role', 'RESPONSABLE')->orderBy('id')->get();

        if ($clients->isEmpty() || $responsibles->isEmpty()) {
            return;
        }

        $workers = [
            ['name' => 'Carlos Mendez', 'document' => '1010101010', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Laura Vargas', 'document' => '2020202020', 'role' => Worker::ROLE_SUPERVISOR],
            ['name' => 'Diego Pardo', 'document' => '3030303030', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Natalia Ruiz', 'document' => '4040404040', 'role' => Worker::ROLE_SUPERVISOR],
            ['name' => 'Juan Ortiz', 'document' => '5050505050', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Sofia Cardenas', 'document' => '6060606060', 'role' => Worker::ROLE_SUPERVISOR],
            ['name' => 'Miguel Quintero', 'document' => '7070707070', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Paula Prieto', 'document' => '8080808080', 'role' => Worker::ROLE_SUPERVISOR],
            ['name' => 'Andres Salazar', 'document' => '9090909090', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Diana Valencia', 'document' => '1111111111', 'role' => Worker::ROLE_SUPERVISOR],
            ['name' => 'Felipe Mejia', 'document' => '1212121212', 'role' => Worker::ROLE_ESCOLTA],
            ['name' => 'Camila Ospina', 'document' => '1313131313', 'role' => Worker::ROLE_SUPERVISOR],
        ];

        foreach ($workers as $index => $worker) {
            $client = $clients[$index % $clients->count()];
            $responsible = $responsibles[$index % $responsibles->count()];

            Worker::updateOrCreate(
                ['document' => $worker['document']],
                [
                    'client_id' => $client->id,
                    'name' => $worker['name'],
                    'document' => $worker['document'],
                    'role' => $worker['role'],
                    'responsible_user_id' => $responsible->id,
                    'notes' => 'Seed de trabajadores',
                ]
            );
        }
    }
}


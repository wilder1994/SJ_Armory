<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::orderBy('id')->get();
        if ($clients->isEmpty()) {
            return;
        }

        $posts = [
            ['name' => 'Sede Administrativa', 'address' => 'Calle 10 # 12-34'],
            ['name' => 'Planta Principal', 'address' => 'Carrera 45 # 67-89'],
            ['name' => 'Bodega Norte', 'address' => 'Avenida 1 # 23-45'],
            ['name' => 'Sucursal Centro', 'address' => 'Calle 50 # 20-10'],
            ['name' => 'Centro Logistico', 'address' => 'Avenida 7 # 88-12'],
            ['name' => 'Parque Industrial', 'address' => 'Kilometro 5 via principal'],
            ['name' => 'Terminal de Carga', 'address' => 'Via 40 # 101-55'],
            ['name' => 'Oficinas Regionales', 'address' => 'Carrera 80 # 15-60'],
            ['name' => 'Zona Franca', 'address' => 'Autopista Norte Km 12'],
            ['name' => 'Plaza Comercial', 'address' => 'Calle 70 # 30-20'],
        ];

        foreach ($posts as $index => $post) {
            $client = $clients[$index % $clients->count()];

            $latOffset = ($index % 5) * 0.002;
            $lngOffset = ($index % 5) * -0.002;

            Post::updateOrCreate(
                ['client_id' => $client->id, 'name' => $post['name']],
                [
                    'client_id' => $client->id,
                    'name' => $post['name'],
                    'address' => $post['address'],
                    'city' => $client->city,
                    'department' => $client->department,
                    'latitude' => $client->latitude ? $client->latitude + $latOffset : null,
                    'longitude' => $client->longitude ? $client->longitude + $lngOffset : null,
                    'notes' => 'Seed de puestos',
                ]
            );
        }
    }
}

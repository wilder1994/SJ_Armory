<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::pluck('id');
        if ($clients->isEmpty()) {
            return;
        }

        $responsibles = User::where('role', 'RESPONSABLE')->get();
        foreach ($responsibles as $responsible) {
            $responsible->clients()->syncWithoutDetaching($clients->all());
        }
    }
}


<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            ResponsibilityLevelSeeder::class,
            AdminUserSeeder::class,
            ClientSeeder::class,
            UserClientSeeder::class,
            WeaponSeeder::class,
            WeaponClientAssignmentSeeder::class,
            WorkerSeeder::class,
            PostSeeder::class,
            WeaponPostAssignmentSeeder::class,
        ]);
    }
}


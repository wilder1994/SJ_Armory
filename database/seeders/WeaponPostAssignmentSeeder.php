<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponPostAssignment;
use Illuminate\Database\Seeder;

class WeaponPostAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::orderBy('id')->get();
        $weapons = Weapon::orderBy('id')->get();
        $admin = User::where('role', 'ADMIN')->orderBy('id')->first();

        if ($posts->isEmpty() || $weapons->isEmpty()) {
            return;
        }

        $postIndex = 0;

        foreach ($weapons as $weapon) {
            $existing = $weapon->postAssignments()->where('is_active', true)->first();
            if ($existing) {
                continue;
            }

            $clientId = $weapon->activeClientAssignment?->client_id;
            $candidatePosts = $clientId
                ? $posts->where('client_id', $clientId)->values()
                : collect();

            if ($candidatePosts->isNotEmpty()) {
                $post = $candidatePosts[$postIndex % $candidatePosts->count()];
            } else {
                $post = $posts[$postIndex % $posts->count()];
            }

            WeaponPostAssignment::create([
                'weapon_id' => $weapon->id,
                'post_id' => $post->id,
                'assigned_by' => $admin?->id,
                'start_at' => now()->toDateString(),
                'is_active' => true,
                'reason' => 'Asignación inicial de prueba',
                'ammo_count' => 30,
                'provider_count' => 1,
            ]);

            $postIndex++;
        }
    }
}

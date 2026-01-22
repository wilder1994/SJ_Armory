<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WeaponAssignmentService
{
    public function assignClient(Weapon $weapon, int $clientId, User $responsible, User $actor, string $startAt, ?string $reason = null): void
    {
        DB::transaction(function () use ($weapon, $clientId, $responsible, $actor, $startAt, $reason) {
            $active = $weapon->clientAssignments()->where('is_active', true)->first();

            if ($active) {
                $active->update([
                    'end_at' => now()->toDateString(),
                    'is_active' => null,
                ]);

                AuditLog::create([
                    'user_id' => $actor->id,
                    'action' => 'client_assignment_closed',
                    'auditable_type' => WeaponClientAssignment::class,
                    'auditable_id' => $active->id,
                    'before' => [
                        'client_id' => $active->client_id,
                        'start_at' => $active->start_at?->toDateString(),
                        'end_at' => null,
                        'is_active' => true,
                    ],
                    'after' => [
                        'client_id' => $active->client_id,
                        'start_at' => $active->start_at?->toDateString(),
                        'end_at' => $active->end_at?->toDateString(),
                        'is_active' => null,
                    ],
                ]);
            }

            $assignment = $weapon->clientAssignments()->create([
                'client_id' => $clientId,
                'responsible_user_id' => $responsible->id,
                'start_at' => $startAt,
                'is_active' => true,
                'assigned_by' => $actor->id,
                'reason' => $reason,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'action' => $active ? 'client_reassigned' : 'client_assigned',
                'auditable_type' => WeaponClientAssignment::class,
                'auditable_id' => $assignment->id,
                'before' => null,
                'after' => [
                    'weapon_id' => $weapon->id,
                    'client_id' => $clientId,
                    'responsible_user_id' => $responsible->id,
                    'start_at' => $startAt,
                ],
            ]);
        });
    }

    public function retireAssignment(Weapon $weapon, User $actor): void
    {
        $active = $weapon->clientAssignments()->where('is_active', true)->first();
        if (!$active) {
            return;
        }

        $active->update([
            'end_at' => now()->toDateString(),
            'is_active' => null,
        ]);

        AuditLog::create([
            'user_id' => $actor->id,
            'action' => 'client_assignment_retired',
            'auditable_type' => WeaponClientAssignment::class,
            'auditable_id' => $active->id,
            'before' => [
                'client_id' => $active->client_id,
                'start_at' => $active->start_at?->toDateString(),
                'end_at' => null,
                'is_active' => true,
            ],
            'after' => [
                'client_id' => $active->client_id,
                'start_at' => $active->start_at?->toDateString(),
                'end_at' => $active->end_at?->toDateString(),
                'is_active' => null,
            ],
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponCustody;
use Illuminate\Support\Facades\DB;

class WeaponCustodyService
{
    public function assignCustody(Weapon $weapon, User $custodian, User $actor, string $startAt, ?string $reason = null): void
    {
        DB::transaction(function () use ($weapon, $custodian, $actor, $startAt, $reason) {
            $active = $weapon->custodies()->where('is_active', true)->first();

            if ($active) {
                $active->update([
                    'end_at' => now(),
                    'is_active' => null,
                ]);

                AuditLog::create([
                    'user_id' => $actor->id,
                    'action' => 'custody_closed',
                    'auditable_type' => WeaponCustody::class,
                    'auditable_id' => $active->id,
                    'before' => [
                        'custodian_user_id' => $active->custodian_user_id,
                        'start_at' => $active->start_at?->toDateTimeString(),
                        'end_at' => null,
                        'is_active' => true,
                    ],
                    'after' => [
                        'custodian_user_id' => $active->custodian_user_id,
                        'start_at' => $active->start_at?->toDateTimeString(),
                        'end_at' => $active->end_at?->toDateTimeString(),
                        'is_active' => null,
                    ],
                ]);
            }

            $custody = $weapon->custodies()->create([
                'custodian_user_id' => $custodian->id,
                'start_at' => $startAt,
                'is_active' => true,
                'assigned_by' => $actor->id,
                'reason' => $reason,
            ]);

            AuditLog::create([
                'user_id' => $actor->id,
                'action' => 'custody_assigned',
                'auditable_type' => WeaponCustody::class,
                'auditable_id' => $custody->id,
                'before' => null,
                'after' => [
                    'weapon_id' => $weapon->id,
                    'custodian_user_id' => $custodian->id,
                    'start_at' => $startAt,
                ],
            ]);
        });
    }
}

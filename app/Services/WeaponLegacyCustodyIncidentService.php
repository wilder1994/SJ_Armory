<?php

namespace App\Services;

use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponIncident;
use App\Support\LegacyCustodyIncidentTypeCode;
use App\Support\PostCustodyRole;

class WeaponLegacyCustodyIncidentService
{
    public function __construct(
        private readonly WeaponIncidentService $incidentService,
    ) {}

    public function closeOpenLegacyIncidents(Weapon $weapon, User $actor, string $custodyRole, ?string $reason = null): int
    {
        $resolutionNote = $reason ?: $this->defaultResolutionNote($custodyRole);

        $incidents = $weapon->openIncidents()
            ->whereHas('type', fn ($query) => $query->whereIn('code', LegacyCustodyIncidentTypeCode::codes()))
            ->with('type')
            ->get();

        $closed = 0;

        foreach ($incidents as $incident) {
            $this->incidentService->close($incident, [
                'status' => WeaponIncident::STATUS_RESOLVED,
                'closure_outcome' => WeaponIncident::OUTCOME_REINTEGRATED,
                'resolution_note' => $resolutionNote,
            ], $actor);
            $closed++;
        }

        return $closed;
    }

    private function defaultResolutionNote(string $custodyRole): string
    {
        return match ($custodyRole) {
            PostCustodyRole::ARMERILLO => __('weapons.legacy_incident_closed_armerillo'),
            PostCustodyRole::ARMERILLO_PARA_MANTENIMIENTO => __('weapons.legacy_incident_closed_para_mantenimiento'),
            PostCustodyRole::ARMERO => __('weapons.legacy_incident_closed_armero'),
            default => __('weapons.legacy_incident_closed_custody'),
        };
    }
}

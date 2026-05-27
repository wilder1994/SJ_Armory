<?php

namespace App\Support;

use App\Models\Weapon;
use App\Models\WeaponIncident;

final class WeaponListStatusResolver
{
    /**
     * @return array{
     *     text: string,
     *     text_class: string,
     *     tone: string,
     *     row_class: string,
     * }
     */
    public static function for(Weapon $weapon): array
    {
        $renewalDocument = $weapon->documents->firstWhere('is_renewal', true)
            ?? $weapon->documents->firstWhere('is_permit', true);
        $renewalAlert = WeaponDocumentAlert::forComplianceDocument($renewalDocument);
        $manualInProcess = $weapon->documents
            ->filter(fn ($doc) => ! ($doc->is_permit || $doc->is_renewal))
            ->first(fn ($doc) => ($doc->status ?? '') === 'En proceso');

        $blockingIncident = self::firstBlockingOpenIncident($weapon);
        if ($blockingIncident) {
            return self::incidentStatus($blockingIncident, 'bg-red-50');
        }

        $custodyRole = $weapon->activeCustodyRole();
        if ($custodyRole !== null) {
            return self::custodyStatus($custodyRole);
        }

        $legacyIncident = self::firstLegacyOpenIncident($weapon);
        if ($legacyIncident) {
            return self::incidentStatus($legacyIncident, '');
        }

        if ($manualInProcess) {
            return [
                'text' => trim(($manualInProcess->document_name ?: __('Documento')).': '.($manualInProcess->observations ?: __('En proceso'))),
                'text_class' => 'text-red-700',
                'tone' => 'danger',
                'row_class' => 'bg-red-100',
            ];
        }

        if (($renewalAlert['observation'] ?? '-') !== '-') {
            return [
                'text' => $renewalAlert['observation'],
                'text_class' => $renewalAlert['text_class'] ?? '',
                'tone' => self::severityTone((int) ($renewalAlert['severity'] ?? 0)),
                'row_class' => $renewalAlert['row_class'] ?? '',
            ];
        }

        $assigned = $weapon->operationalDisplayClient() !== null;

        return [
            'text' => $assigned ? __('Asignada') : __('Sin destino'),
            'text_class' => '',
            'tone' => $assigned || $weapon->activePostAssignment || $weapon->activeWorkerAssignment ? 'ok' : 'neutral',
            'row_class' => '',
        ];
    }

    /**
     * Novedad abierta relevante para columna de exportación / expediente (excluye legado de custodia).
     */
    public static function openIncidentLabelForExport(Weapon $weapon): string
    {
        $incident = self::firstBlockingOpenIncident($weapon)
            ?? self::firstNonLegacyOpenIncident($weapon);

        if (! $incident) {
            return __('Sin novedades');
        }

        return trim(
            ($incident->type?->name ?? __('Novedad'))
            .($incident->modality ? ' / '.$incident->modality->name : ''),
        );
    }

    private static function firstBlockingOpenIncident(Weapon $weapon): ?WeaponIncident
    {
        if (! $weapon->relationLoaded('openIncidents')) {
            return $weapon->openIncidents()
                ->with(['type', 'modality'])
                ->get()
                ->first(fn (WeaponIncident $incident) => $incident->blocksOperationalAvailability());
        }

        return $weapon->openIncidents
            ->first(fn (WeaponIncident $incident) => $incident->blocksOperationalAvailability());
    }

    private static function firstLegacyOpenIncident(Weapon $weapon): ?WeaponIncident
    {
        return $weapon->openIncidents
            ->first(fn (WeaponIncident $incident) => LegacyCustodyIncidentTypeCode::isLegacy($incident->type?->code));
    }

    private static function firstNonLegacyOpenIncident(Weapon $weapon): ?WeaponIncident
    {
        return $weapon->openIncidents
            ->first(fn (WeaponIncident $incident) => ! LegacyCustodyIncidentTypeCode::isLegacy($incident->type?->code));
    }

    /**
     * @return array{text: string, text_class: string, tone: string, row_class: string}
     */
    private static function incidentStatus(WeaponIncident $incident, string $rowClass): array
    {
        return [
            'text' => trim(
                ($incident->type?->name ?? __('Novedad'))
                .($incident->modality ? ': '.$incident->modality->name : ''),
            ),
            'text_class' => 'text-red-700',
            'tone' => 'danger',
            'row_class' => $rowClass,
        ];
    }

    /**
     * @return array{text: string, text_class: string, tone: string, row_class: string}
     */
    private static function custodyStatus(string $custodyRole): array
    {
        return match ($custodyRole) {
            PostCustodyRole::ARMERILLO => [
                'text' => PostCustodyRole::label($custodyRole),
                'text_class' => 'text-emerald-700',
                'tone' => 'ok',
                'row_class' => '',
            ],
            PostCustodyRole::ARMERILLO_PARA_MANTENIMIENTO => [
                'text' => PostCustodyRole::label($custodyRole),
                'text_class' => 'text-amber-700',
                'tone' => 'warning',
                'row_class' => 'bg-amber-50',
            ],
            PostCustodyRole::ARMERO => [
                'text' => PostCustodyRole::label($custodyRole),
                'text_class' => 'text-violet-700',
                'tone' => 'warning',
                'row_class' => 'bg-violet-50',
            ],
            default => [
                'text' => PostCustodyRole::label($custodyRole),
                'text_class' => '',
                'tone' => 'neutral',
                'row_class' => '',
            ],
        };
    }

    private static function severityTone(int $severity): string
    {
        if ($severity >= 3) {
            return 'danger';
        }

        if ($severity >= 2) {
            return 'warning';
        }

        if ($severity >= 1) {
            return 'notice';
        }

        return 'ok';
    }
}

<?php

namespace App\Console\Commands;

use App\Models\IncidentType;
use App\Models\WeaponDocument;
use App\Models\WeaponIncident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillWeaponIncidentsFromDocuments extends Command
{
    protected $signature = 'incidents:backfill-from-documents {--dry-run : Muestra cuántos registros se crearían sin escribir en la base}';

    protected $description = 'Migra documentos manuales existentes hacia weapon_incidents sin duplicar registros ya enlazados.';

    public function handle(): int
    {
        $typeMap = IncidentType::query()
            ->pluck('id', DB::raw('lower(name)'))
            ->mapWithKeys(fn ($id, $name) => [trim((string) $name) => (int) $id]);

        $documents = WeaponDocument::query()
            ->where('is_permit', false)
            ->where('is_renewal', false)
            ->whereNotNull('observations')
            ->whereNotIn('id', function ($query) {
                $query->select('source_document_id')
                    ->from('weapon_incidents')
                    ->whereNotNull('source_document_id');
            })
            ->orderBy('id')
            ->get();

        $eligible = $documents->filter(function (WeaponDocument $document) use ($typeMap) {
            return $typeMap->has(mb_strtolower(trim((string) $document->observations)));
        })->values();

        if ($eligible->isEmpty()) {
            $this->info('No hay documentos manuales pendientes por migrar.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Documentos elegibles para migración: ' . $eligible->count());

            return self::SUCCESS;
        }

        $created = 0;

        DB::transaction(function () use ($eligible, $typeMap, &$created) {
            foreach ($eligible as $document) {
                $typeId = $typeMap[mb_strtolower(trim((string) $document->observations))] ?? null;

                if (!$typeId) {
                    continue;
                }

                WeaponIncident::query()->create([
                    'weapon_id' => $document->weapon_id,
                    'incident_type_id' => $typeId,
                    'incident_modality_id' => null,
                    'status' => ($document->status === 'En proceso')
                        ? WeaponIncident::STATUS_OPEN
                        : WeaponIncident::STATUS_RESOLVED,
                    'observation' => $document->document_name ?: trim((string) $document->observations),
                    'note' => 'Migrado desde documento manual existente.',
                    'event_at' => $document->created_at ?? now(),
                    'reported_at' => $document->created_at ?? now(),
                    'reported_by' => $document->file?->uploaded_by,
                    'source_document_id' => $document->id,
                    'attachment_file_id' => $document->file_id,
                    'resolved_at' => ($document->status === 'En proceso') ? null : ($document->updated_at ?? $document->created_at ?? now()),
                    'resolved_by' => null,
                    'resolution_note' => ($document->status === 'En proceso') ? null : 'Caso migrado como cerrado desde documento manual.',
                    'created_at' => $document->created_at ?? now(),
                    'updated_at' => $document->updated_at ?? now(),
                ]);

                $created++;
            }
        });

        $this->info("Novedades creadas: {$created}");

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Models\File;
use App\Models\Weapon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WeaponDocumentService
{
    public function __construct(
        private readonly RevalidationDocumentBuilder $builder,
    ) {
    }

    public function syncPermitDocument(Weapon $weapon): void
    {
        $permitFile = $weapon->permitFile()->first();
        if (!$permitFile) {
            return;
        }

        $document = $weapon->documents()->where('is_permit', true)->first();
        $payload = [
            'file_id' => $permitFile->id,
            'document_name' => 'Permiso',
            'document_number' => $weapon->permit_number,
            'permit_kind' => $weapon->permit_type,
            'valid_until' => $weapon->permit_expires_at,
            'observations' => null,
            'status' => null,
            'is_permit' => true,
            'is_renewal' => false,
        ];

        if ($document) {
            $document->update($payload);
        } else {
            $weapon->documents()->create($payload);
        }
    }

    public function syncRenewalDocument(Weapon $weapon): void
    {
        $document = $weapon->documents()->where('is_renewal', true)->first();

        $fileName = 'revalidacion_' . $weapon->internal_code . '.docx';
        $path = 'weapons/' . $weapon->id . '/documents/' . $fileName;
        $absolutePath = Storage::disk('local')->path($path);

        $this->builder->buildForWeapon($weapon, $absolutePath);

        DB::transaction(function () use ($weapon, $document, $path, $fileName) {
            $storedFile = File::create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $fileName,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size' => Storage::disk('local')->size($path),
                'checksum' => hash_file('sha256', Storage::disk('local')->path($path)),
                'uploaded_by' => null,
            ]);

            $payload = [
                'file_id' => $storedFile->id,
                'document_name' => 'Revalidación',
                'document_number' => null,
                'permit_kind' => $weapon->permit_type,
                'valid_until' => $weapon->permit_expires_at,
                'observations' => null,
                'status' => null,
                'is_permit' => false,
                'is_renewal' => true,
            ];

            if ($document) {
                $oldFile = $document->file;
                $document->update($payload);

                if ($oldFile) {
                    $samePath = $oldFile->disk === $storedFile->disk
                        && $oldFile->path === $storedFile->path;

                    if (!$samePath) {
                        Storage::disk($oldFile->disk)->delete($oldFile->path);
                    }

                    $oldFile->delete();
                }
            } else {
                $weapon->documents()->create($payload);
            }
        });
    }

    public function buildBatchDocument(iterable $weapons): array
    {
        $fileName = 'revalidacion_masiva_' . now()->format('Ymd_His') . '.docx';
        $absolutePath = storage_path('app/tmp/' . $fileName);

        $this->builder->buildForWeapons($weapons, $absolutePath);

        return [
            'file_name' => $fileName,
            'path' => $absolutePath,
        ];
    }
}

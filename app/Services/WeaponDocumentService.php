<?php

namespace App\Services;

use App\Models\File;
use App\Models\Weapon;
use App\Models\WeaponDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class WeaponDocumentService
{
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

        $fileName = 'renovacion_' . $weapon->internal_code . '.docx';
        $path = 'weapons/' . $weapon->id . '/documents/' . $fileName;
        $tempPath = storage_path('app/tmp/' . uniqid('renewal_', true) . '.docx');

        $this->buildRenewalDocument($weapon, $tempPath);
        $absolutePath = Storage::disk('local')->path($path);
        FileFacade::ensureDirectoryExists(dirname($absolutePath));
        if (!@copy($tempPath, $absolutePath)) {
            @unlink($tempPath);
            throw new \RuntimeException('No se pudo generar el documento de renovacion.');
        }
        @unlink($tempPath);

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
                'document_name' => 'Renovacion',
                'document_number' => null,
                'permit_kind' => $weapon->permit_type,
                'valid_until' => $weapon->permit_expires_at,
                'observations' => 'Revalidar',
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

    private function buildRenewalDocument(Weapon $weapon, string $outputPath): void
    {
        $templatePath = resource_path('templates/PLANTILLA_REVALIDACION.docx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('No se encontro la plantilla de renovacion.');
        }
        $processor = new TemplateProcessor($templatePath);

        $photos = $weapon->photos()->with('file')->get()->keyBy('description');
        $right = $this->imagePath($photos->get('lado_derecho'));
        $left = $this->imagePath($photos->get('lado_izquierdo'));
        $mark = $this->imagePath($photos->get('canon_disparador_marca'));
        $serial = $this->imagePath($photos->get('serie'));
        $imprint = $this->imagePath($photos->get('aseo'));

        $title = trim('PISTOLA ' . ($weapon->brand ?? '') . ' ' . ($weapon->serial_number ?? ''));
        $title = strtoupper(trim(preg_replace('/\s+/', ' ', $title)));

        $processor->setValue('titulo', $title);
        $processor->setImageValue('foto_derecha', $this->imageSpec($right));
        $processor->setImageValue('foto_izquierda', $this->imageSpec($left));
        $processor->setImageValue('foto_marca', $this->imageSpec($mark));
        $processor->setImageValue('foto_serie', $this->imageSpec($serial));
        $processor->setImageValue('impronta', $this->imprintSpec($imprint));

        $processor->saveAs($outputPath);
    }

    private function imagePath($photo): ?string
    {
        if (!$photo || !$photo->file) {
            return null;
        }

        $disk = $photo->file->disk;
        $path = $photo->file->path;
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->path($path);
    }

    private function imageSpec(?string $path): array
    {
        $widthPx = $this->cmToPx(7.2);
        $heightPx = $this->cmToPx(5.9);
        $blank = storage_path('app/tmp/blank.png');
        if (!file_exists($blank)) {
            @mkdir(dirname($blank), 0777, true);
            file_put_contents($blank, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/xcAAn8B9x2XWAAAAABJRU5ErkJggg=='
            ));
        }

        $safePath = $path && file_exists($path) ? $path : $blank;

        return [
            'path' => $safePath,
            'width' => $widthPx,
            'height' => $heightPx,
            'ratio' => false,
        ];
    }

    private function imprintSpec(?string $path): array
    {
        $widthPx = $this->cmToPx(5.9);
        $heightPx = $this->cmToPx(1.4);
        $blank = storage_path('app/tmp/blank.png');
        if (!file_exists($blank)) {
            @mkdir(dirname($blank), 0777, true);
            file_put_contents($blank, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/xcAAn8B9x2XWAAAAABJRU5ErkJggg=='
            ));
        }

        $safePath = $path && file_exists($path) ? $path : $blank;

        return [
            'path' => $safePath,
            'width' => $widthPx,
            'height' => $heightPx,
            'ratio' => false,
        ];
    }

    private function cmToPx(float $cm): int
    {
        return (int)round(($cm / 2.54) * 96);
    }
}

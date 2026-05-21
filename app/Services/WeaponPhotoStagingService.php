<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\TemporaryPhotoAccessGrant;
use App\Models\TemporaryPhotoUser;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponPhoto;
use App\Models\WeaponPhotoStaging;
use App\Support\RevistaWeaponPhotoSlots;
use App\Services\WeaponDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class WeaponPhotoStagingService
{
    public function __construct(
        private readonly WeaponDocumentService $documentService,
    ) {
    }

    public function stagingCount(TemporaryPhotoUser $temporaryUser, Weapon $weapon): int
    {
        return WeaponPhotoStaging::query()
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->count();
    }

    public function isStagingComplete(TemporaryPhotoUser $temporaryUser, Weapon $weapon): bool
    {
        return $this->stagingCount($temporaryUser, $weapon) >= RevistaWeaponPhotoSlots::requiredCount();
    }

    /**
     * @return array<string, WeaponPhotoStaging>
     */
    public function stagingByDescription(TemporaryPhotoUser $temporaryUser, Weapon $weapon): array
    {
        return WeaponPhotoStaging::query()
            ->with('file')
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get()
            ->keyBy('description')
            ->all();
    }

    public function storeForGuest(
        TemporaryPhotoAccessGrant $grant,
        Weapon $weapon,
        UploadedFile $file,
        string $description,
    ): WeaponPhotoStaging {
        if (! $grant->isCurrentlyValid()) {
            throw ValidationException::withMessages([
                'photo' => __('El acceso temporal ha expirado o fue revocado.'),
            ]);
        }

        $grant->loadMissing('temporaryPhotoUser');
        $temporaryUser = $grant->temporaryPhotoUser;

        if (! in_array($description, RevistaWeaponPhotoSlots::keys(), true)) {
            throw ValidationException::withMessages([
                'description' => __('Descripción de foto no válida.'),
            ]);
        }

        $allowedWeaponIds = $grant->weapons()->pluck('weapon_id')->all();
        if (! in_array($weapon->id, $allowedWeaponIds, true)) {
            abort(403);
        }

        $path = $file->store(
            'weapons/'.$weapon->id.'/staging/'.$temporaryUser->id,
            'public'
        );

        try {
            return DB::transaction(function () use ($temporaryUser, $weapon, $file, $path, $description) {
                $existing = WeaponPhotoStaging::query()
                    ->where('temporary_photo_user_id', $temporaryUser->id)
                    ->where('weapon_id', $weapon->id)
                    ->where('description', $description)
                    ->first();

                if ($existing?->file) {
                    Storage::disk($existing->file->disk)->delete($existing->file->path);
                    $existing->file->delete();
                    $existing->delete();
                }

                $storedFile = File::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => null,
                ]);

                return WeaponPhotoStaging::create([
                    'temporary_photo_user_id' => $temporaryUser->id,
                    'weapon_id' => $weapon->id,
                    'description' => $description,
                    'file_id' => $storedFile->id,
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function approve(User $reviewer, TemporaryPhotoUser $temporaryUser, Weapon $weapon): void
    {
        $stagingRows = WeaponPhotoStaging::query()
            ->with('file')
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get();

        if ($stagingRows->count() < RevistaWeaponPhotoSlots::requiredCount()) {
            throw ValidationException::withMessages([
                'photos' => __('Faltan fotos en revisión para actualizar las imágenes oficiales.'),
            ]);
        }

        DB::transaction(function () use ($reviewer, $weapon, $stagingRows, $temporaryUser) {
            foreach ($stagingRows as $staging) {
                $this->promoteStagingRowToOfficial($reviewer, $weapon, $staging);
            }

            WeaponPhotoStaging::query()
                ->where('temporary_photo_user_id', $temporaryUser->id)
                ->where('weapon_id', $weapon->id)
                ->whereIn('description', RevistaWeaponPhotoSlots::keys())
                ->delete();

            AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'approve_revista_staging_photos',
                'auditable_type' => Weapon::class,
                'auditable_id' => $weapon->id,
                'before' => null,
                'after' => [
                    'temporary_photo_user_id' => $temporaryUser->id,
                    'count' => $stagingRows->count(),
                ],
            ]);
        });

        $this->documentService->syncRenewalDocument($weapon);
    }

    public function reject(User $reviewer, TemporaryPhotoUser $temporaryUser, Weapon $weapon): void
    {
        $stagingRows = WeaponPhotoStaging::query()
            ->with('file')
            ->where('temporary_photo_user_id', $temporaryUser->id)
            ->where('weapon_id', $weapon->id)
            ->whereIn('description', RevistaWeaponPhotoSlots::keys())
            ->get();

        DB::transaction(function () use ($reviewer, $weapon, $stagingRows, $temporaryUser) {
            foreach ($stagingRows as $staging) {
                if ($staging->file) {
                    Storage::disk($staging->file->disk)->delete($staging->file->path);
                    $staging->file->delete();
                }
                $staging->delete();
            }

            AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'reject_revista_staging_photos',
                'auditable_type' => Weapon::class,
                'auditable_id' => $weapon->id,
                'before' => null,
                'after' => [
                    'temporary_photo_user_id' => $temporaryUser->id,
                ],
            ]);
        });
    }

    private function promoteStagingRowToOfficial(User $reviewer, Weapon $weapon, WeaponPhotoStaging $staging): void
    {
        $stagingFile = $staging->file;
        if (! $stagingFile) {
            return;
        }

        $existingPhoto = $weapon->photos()
            ->with('file')
            ->where('description', $staging->description)
            ->first();

        if ($existingPhoto) {
            if ($existingPhoto->file) {
                Storage::disk($existingPhoto->file->disk)->delete($existingPhoto->file->path);
                $existingPhoto->file->delete();
            }
            $existingPhoto->delete();
        }

        $extension = pathinfo($stagingFile->path, PATHINFO_EXTENSION) ?: 'jpg';
        $officialPath = 'weapons/'.$weapon->id.'/photos/revista_'.uniqid('', true).'.'.$extension;

        Storage::disk('public')->copy($stagingFile->path, $officialPath);

        $officialFile = File::create([
            'disk' => 'public',
            'path' => $officialPath,
            'original_name' => $stagingFile->original_name,
            'mime_type' => $stagingFile->mime_type,
            'size' => $stagingFile->size,
            'checksum' => $stagingFile->checksum,
            'uploaded_by' => $reviewer->id,
        ]);

        $photo = $weapon->photos()->create([
            'file_id' => $officialFile->id,
            'description' => $staging->description,
        ]);

        Storage::disk($stagingFile->disk)->delete($stagingFile->path);
        $stagingFile->delete();

        AuditLog::create([
            'user_id' => $reviewer->id,
            'action' => 'upload_photo',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => null,
            'after' => [
                'photo_id' => $photo->id,
                'file_id' => $officialFile->id,
                'description' => $photo->description,
                'source' => 'revista_staging',
            ],
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\User;
use App\Models\Vest;
use App\Models\VestPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VestPhotoService
{
    /**
     * @param  array<int, UploadedFile|null>  $indexedPhotos
     */
    public function storeIndexedPhotos(Vest $vest, array $indexedPhotos, ?User $user): void
    {
        foreach (array_keys(VestPhoto::DESCRIPTIONS) as $index => $description) {
            $photoFile = $indexedPhotos[$index] ?? null;

            if (! $photoFile instanceof UploadedFile) {
                continue;
            }

            $this->storeOrReplacePhoto($vest, $description, $photoFile, $user);
        }
    }

    public function storeOrReplacePhoto(Vest $vest, string $description, UploadedFile $photoFile, ?User $user): VestPhoto
    {
        $path = $photoFile->store('vests/'.$vest->id.'/photos', 'public');

        try {
            return DB::transaction(function () use ($vest, $description, $photoFile, $path, $user) {
                $storedFile = File::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $photoFile->getClientOriginalName(),
                    'mime_type' => $photoFile->getClientMimeType(),
                    'size' => $photoFile->getSize(),
                    'checksum' => hash_file('sha256', $photoFile->getRealPath()),
                    'uploaded_by' => $user?->id,
                ]);

                $existingPhoto = $vest->photos()->with('file')
                    ->where('description', $description)
                    ->first();

                if ($existingPhoto) {
                    $oldFile = $existingPhoto->file;
                    $existingPhoto->update(['file_id' => $storedFile->id]);

                    if ($oldFile) {
                        Storage::disk($oldFile->disk)->delete($oldFile->path);
                        $oldFile->delete();
                    }

                    $photo = $existingPhoto->fresh();
                    $action = 'update_vest_photo';
                } else {
                    $photo = $vest->photos()->create([
                        'file_id' => $storedFile->id,
                        'description' => $description,
                    ]);
                    $action = 'upload_vest_photo';
                }

                AuditLog::create([
                    'user_id' => $user?->id,
                    'action' => $action,
                    'auditable_type' => Vest::class,
                    'auditable_id' => $vest->id,
                    'before' => null,
                    'after' => [
                        'photo_id' => $photo->id,
                        'file_id' => $storedFile->id,
                        'description' => $photo->description,
                    ],
                ]);

                return $photo;
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }
}

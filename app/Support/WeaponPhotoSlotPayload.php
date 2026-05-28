<?php

namespace App\Support;

use App\Models\Weapon;
use App\Models\WeaponPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class WeaponPhotoSlotPayload
{
    public static function json(array $slot): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'slot' => $slot,
        ]);
    }

    public static function forWeaponPhoto(WeaponPhoto $photo): array
    {
        $photo->loadMissing('file');
        $label = WeaponPhoto::DESCRIPTIONS[$photo->description] ?? $photo->description;

        return [
            'id' => $photo->id,
            'type' => 'weapon',
            'description' => $photo->description,
            'label' => $label,
            'url' => self::fileUrl($photo->file),
            'created_at' => $photo->created_at?->format('Y-m-d'),
            'empty' => false,
            'destroy_url' => route('weapons.photos.destroy', [$photo->weapon_id, $photo->id]),
        ];
    }

    public static function emptyWeaponSlot(string $description, string $label): array
    {
        return [
            'id' => null,
            'type' => 'weapon',
            'description' => $description,
            'label' => $label,
            'url' => null,
            'created_at' => null,
            'empty' => true,
            'destroy_url' => null,
        ];
    }

    public static function forPermit(Weapon $weapon): array
    {
        $weapon->loadMissing('permitFile');

        return [
            'id' => null,
            'type' => 'permit',
            'description' => null,
            'label' => __('Permiso (frente)'),
            'url' => $weapon->permitFile
                ? route('weapons.permit', $weapon).'?v='.$weapon->permitFile->updated_at?->timestamp
                : null,
            'created_at' => $weapon->permitFile?->created_at?->format('Y-m-d'),
            'empty' => ! $weapon->permitFile,
            'destroy_url' => null,
        ];
    }

    private static function fileUrl(?\App\Models\File $file): ?string
    {
        if (! $file) {
            return null;
        }

        $version = $file->updated_at?->timestamp ?? time();

        return Storage::disk($file->disk)->url($file->path).'?v='.$version;
    }
}

<?php

namespace App\Support;

use App\Models\VestPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class VestPhotoSlotPayload
{
    public static function json(array $slot): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'slot' => $slot,
        ]);
    }

    public static function forVestPhoto(VestPhoto $photo): array
    {
        $photo->loadMissing('file');
        $label = VestPhoto::DESCRIPTIONS[$photo->description] ?? $photo->description;

        return [
            'id' => $photo->id,
            'type' => 'vest',
            'description' => $photo->description,
            'label' => $label,
            'url' => self::fileUrl($photo->file),
            'created_at' => $photo->created_at?->format('Y-m-d'),
            'empty' => false,
            'destroy_url' => route('vests.photos.destroy', [$photo->vest_id, $photo->id]),
        ];
    }

    public static function emptySlot(string $description, string $label): array
    {
        return [
            'id' => null,
            'type' => 'vest',
            'description' => $description,
            'label' => $label,
            'url' => null,
            'created_at' => null,
            'empty' => true,
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

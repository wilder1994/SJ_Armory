<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Weapon;
use App\Models\WeaponPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WeaponPhotoController extends Controller
{
    public function store(Request $request, Weapon $weapon)
    {
        $this->authorize('update', $weapon);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $file = $data['photo'];
        $path = $file->store('weapons/' . $weapon->id . '/photos', 'public');

        $storedFile = File::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $request->user()?->id,
        ]);

        $isPrimary = (bool)($data['is_primary'] ?? false);
        if ($isPrimary) {
            $weapon->photos()->update(['is_primary' => false]);
        }

        $photo = $weapon->photos()->create([
            'file_id' => $storedFile->id,
            'is_primary' => $isPrimary,
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'upload_photo',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => null,
            'after' => [
                'photo_id' => $photo->id,
                'file_id' => $storedFile->id,
            ],
        ]);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Foto cargada.');
    }

    public function setPrimary(Weapon $weapon, WeaponPhoto $photo, Request $request)
    {
        $this->authorize('update', $weapon);

        if ($photo->weapon_id !== $weapon->id) {
            abort(404);
        }

        $weapon->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'set_primary_photo',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => null,
            'after' => [
                'photo_id' => $photo->id,
            ],
        ]);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Foto primaria actualizada.');
    }

    public function destroy(Weapon $weapon, WeaponPhoto $photo)
    {
        $this->authorize('update', $weapon);

        if ($photo->weapon_id !== $weapon->id) {
            abort(404);
        }

        $file = $photo->file;
        $photo->delete();

        if ($file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        return redirect()->route('weapons.show', $weapon)->with('status', 'Foto eliminada.');
    }
}

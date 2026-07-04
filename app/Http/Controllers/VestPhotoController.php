<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Vest;
use App\Models\VestPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class VestPhotoController extends Controller
{
    public function store(Request $request, Vest $vest)
    {
        $this->authorize('updatePhotos', $vest);

        $descriptions = array_keys(VestPhoto::DESCRIPTIONS);
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'description' => ['required', Rule::in($descriptions)],
        ]);

        $file = $data['photo'];
        $path = $file->store('vests/'.$vest->id.'/photos', 'public');

        try {
            DB::transaction(function () use ($data, $file, $path, $request, $vest) {
                $storedFile = File::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $request->user()?->id,
                ]);

                $existingPhoto = $vest->photos()->with('file')
                    ->where('description', $data['description'])
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
                        'description' => $data['description'],
                    ]);
                    $action = 'upload_vest_photo';
                }

                AuditLog::create([
                    'user_id' => $request->user()?->id,
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
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return redirect()->route('vests.show', $vest)->with('status', __('Foto cargada.'));
    }

    public function update(Request $request, Vest $vest, VestPhoto $photo)
    {
        $this->authorize('updatePhotos', $vest);

        if ($photo->vest_id !== $vest->id) {
            abort(404);
        }

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $file = $data['photo'];
        $path = $file->store('vests/'.$vest->id.'/photos', 'public');

        try {
            DB::transaction(function () use ($file, $path, $request, $photo, $vest) {
                $storedFile = File::create([
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $request->user()?->id,
                ]);

                $oldFile = $photo->file;
                $photo->update(['file_id' => $storedFile->id]);

                if ($oldFile) {
                    Storage::disk($oldFile->disk)->delete($oldFile->path);
                    $oldFile->delete();
                }

                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'action' => 'update_vest_photo',
                    'auditable_type' => Vest::class,
                    'auditable_id' => $vest->id,
                    'before' => null,
                    'after' => [
                        'photo_id' => $photo->id,
                        'file_id' => $storedFile->id,
                        'description' => $photo->description,
                    ],
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Vest $vest, VestPhoto $photo)
    {
        $this->authorize('updatePhotos', $vest);

        if ($photo->vest_id !== $vest->id) {
            abort(404);
        }

        $file = $photo->file;
        $photo->delete();

        if ($file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        return redirect()->route('vests.show', $vest)->with('status', __('Foto eliminada.'));
    }
}

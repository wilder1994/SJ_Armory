<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Weapon;
use App\Models\WeaponDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class WeaponDocumentController extends Controller
{
    public function store(Request $request, Weapon $weapon)
    {
        $this->authorize('update', $weapon);

        $data = $request->validate([
            'document' => ['required', 'file', 'max:10240'],
            'valid_until' => ['nullable', 'date'],
            'observations' => ['nullable', 'string'],
        ]);

        $file = $data['document'];
        $path = $file->store('weapons/' . $weapon->id . '/documents', 'local');

        try {
            DB::transaction(function () use ($data, $file, $path, $request, $weapon) {
                $storedFile = File::create([
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $request->user()?->id,
                ]);

                $document = $weapon->documents()->create([
                    'file_id' => $storedFile->id,
                    'valid_until' => $data['valid_until'] ?? null,
                    'observations' => $data['observations'] ?? null,
                ]);

                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'action' => 'upload_document',
                    'auditable_type' => Weapon::class,
                    'auditable_id' => $weapon->id,
                    'before' => null,
                    'after' => [
                        'document_id' => $document->id,
                        'file_id' => $storedFile->id,
                    ],
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return redirect()->route('weapons.show', $weapon)->with('status', 'Documento cargado.');
    }

    public function download(Weapon $weapon, WeaponDocument $document)
    {
        $this->authorize('view', $weapon);

        if ($document->weapon_id !== $weapon->id) {
            abort(404);
        }

        $file = $document->file;
        if (!$file) {
            abort(404);
        }

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function destroy(Weapon $weapon, WeaponDocument $document)
    {
        $this->authorize('update', $weapon);

        if ($document->weapon_id !== $weapon->id) {
            abort(404);
        }

        $file = $document->file;
        $document->delete();

        if ($file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        return redirect()->route('weapons.show', $weapon)->with('status', 'Documento eliminado.');
    }
}

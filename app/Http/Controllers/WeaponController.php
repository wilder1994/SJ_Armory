<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Weapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class WeaponController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Weapon::class, 'weapon');
    }

    public function index(Request $request)
    {
        $query = Weapon::query();
        $user = $request->user();

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('clientAssignments', function ($assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id)->where('is_active', true);
            });
        }

        $weapons = $query->with(['activeClientAssignment.client'])->orderByDesc('id')->paginate(15)->withQueryString();

        return view('weapons.index', compact('weapons'));
    }

    public function create()
    {
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.create', compact('ownershipTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internal_code' => ['nullable', 'string', 'max:100', 'unique:weapons,internal_code'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:weapons,serial_number'],
            'weapon_type' => ['required', 'string', 'max:100'],
            'caliber' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'ownership_type' => ['required', 'in:' . implode(',', array_keys($this->ownershipOptions()))],
            'ownership_entity' => ['nullable', 'string', 'max:255'],
            'permit_type' => ['nullable', 'string', 'max:100'],
            'permit_number' => ['nullable', 'string', 'max:100'],
            'permit_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'file', 'image', 'max:5120'],
            'permit_photo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if (empty($data['internal_code'])) {
            $data['internal_code'] = $this->generateInternalCode();
        }

        $photos = $request->file('photos', []);
        $permitPhoto = $request->file('permit_photo');
        $storedPaths = [];
        $storedPermitPath = null;
        $weapon = null;

        try {
            DB::transaction(function () use (&$weapon, $data, $photos, $permitPhoto, $request, &$storedPaths, &$storedPermitPath) {
                $weapon = Weapon::create($data);

                if ($permitPhoto) {
                    $storedPermitPath = $permitPhoto->store('weapons/' . $weapon->id . '/permits', 'local');
                    $storedFile = File::create([
                        'disk' => 'local',
                        'path' => $storedPermitPath,
                        'original_name' => $permitPhoto->getClientOriginalName(),
                        'mime_type' => $permitPhoto->getClientMimeType(),
                        'size' => $permitPhoto->getSize(),
                        'checksum' => hash_file('sha256', $permitPhoto->getRealPath()),
                        'uploaded_by' => $request->user()?->id,
                    ]);

                    $weapon->update(['permit_file_id' => $storedFile->id]);
                }

                if (!$photos) {
                    return;
                }

                $isPrimary = true;
                foreach ($photos as $photoFile) {
                    if (!$photoFile) {
                        continue;
                    }

                    $path = $photoFile->store('weapons/' . $weapon->id . '/photos', 'public');
                    $storedPaths[] = $path;

                    $storedFile = File::create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $photoFile->getClientOriginalName(),
                        'mime_type' => $photoFile->getClientMimeType(),
                        'size' => $photoFile->getSize(),
                        'checksum' => hash_file('sha256', $photoFile->getRealPath()),
                        'uploaded_by' => $request->user()?->id,
                    ]);

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

                    $isPrimary = false;
                }
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            if ($storedPermitPath) {
                Storage::disk('local')->delete($storedPermitPath);
            }
            throw $e;
        }

        return redirect()->route('weapons.show', $weapon)->with('status', 'Arma creada.');
    }

    public function show(Weapon $weapon)
    {
        $weapon->load([
            'photos' => function ($query) {
                $query->orderByDesc('is_primary')->orderBy('id');
            },
            'photos.file',
            'documents' => function ($query) {
                $query->orderByDesc('id');
            },
            'documents.file',
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
        ]);
        $ownershipTypes = $this->ownershipOptions();
        $responsibles = collect();
        $portfolioClients = collect();

        if (request()->user()?->isAdmin()) {
            $responsibles = \App\Models\User::where('role', 'RESPONSABLE')->orderBy('name')->get();
            $portfolioClients = \App\Models\Client::orderBy('name')->get();
        } elseif (request()->user()?->isResponsible()) {
            $responsibles = collect([request()->user()]);
            $portfolioClients = request()->user()?->clients()->orderBy('name')->get() ?? collect();
        }

        return view('weapons.show', compact('weapon', 'ownershipTypes', 'responsibles', 'portfolioClients'));
    }

    public function permitPhoto(Weapon $weapon)
    {
        $this->authorize('view', $weapon);

        $permitFile = $weapon->permitFile;
        if (!$permitFile) {
            abort(404);
        }

        return Storage::disk($permitFile->disk)->response(
            $permitFile->path,
            $permitFile->original_name ?? 'permiso'
        );
    }

    public function edit(Weapon $weapon)
    {
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.edit', compact('weapon', 'ownershipTypes'));
    }

    public function update(Request $request, Weapon $weapon)
    {
        $data = $request->validate([
            'internal_code' => ['required', 'string', 'max:100', 'unique:weapons,internal_code,' . $weapon->id],
            'serial_number' => ['required', 'string', 'max:100', 'unique:weapons,serial_number,' . $weapon->id],
            'weapon_type' => ['required', 'string', 'max:100'],
            'caliber' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'ownership_type' => ['required', 'in:' . implode(',', array_keys($this->ownershipOptions()))],
            'ownership_entity' => ['nullable', 'string', 'max:255'],
            'permit_type' => ['nullable', 'string', 'max:100'],
            'permit_number' => ['nullable', 'string', 'max:100'],
            'permit_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'file', 'image', 'max:5120'],
            'permit_photo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $photos = collect($request->file('photos', []))->filter()->values();
        $permitPhoto = $request->file('permit_photo');
        $storedPaths = [];
        $storedPermitPath = null;

        try {
            DB::transaction(function () use ($data, $photos, $permitPhoto, $request, $weapon, &$storedPaths, &$storedPermitPath) {
                $existingPhotos = $photos->isNotEmpty()
                    ? $weapon->photos()->with('file')->get()
                    : collect();

                if ($permitPhoto) {
                    $storedPermitPath = $permitPhoto->store('weapons/' . $weapon->id . '/permits', 'local');
                    $storedFile = File::create([
                        'disk' => 'local',
                        'path' => $storedPermitPath,
                        'original_name' => $permitPhoto->getClientOriginalName(),
                        'mime_type' => $permitPhoto->getClientMimeType(),
                        'size' => $permitPhoto->getSize(),
                        'checksum' => hash_file('sha256', $permitPhoto->getRealPath()),
                        'uploaded_by' => $request->user()?->id,
                    ]);

                    $data['permit_file_id'] = $storedFile->id;
                }

                $oldPermitFile = $weapon->permitFile;
                $weapon->update($data);

                if ($permitPhoto && $oldPermitFile) {
                    Storage::disk($oldPermitFile->disk)->delete($oldPermitFile->path);
                    $oldPermitFile->delete();
                }

                if ($photos->isEmpty()) {
                    return;
                }

                $isPrimary = true;
                foreach ($photos as $photoFile) {
                    if (!$photoFile) {
                        continue;
                    }

                    $path = $photoFile->store('weapons/' . $weapon->id . '/photos', 'public');
                    $storedPaths[] = $path;

                    $storedFile = File::create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $photoFile->getClientOriginalName(),
                        'mime_type' => $photoFile->getClientMimeType(),
                        'size' => $photoFile->getSize(),
                        'checksum' => hash_file('sha256', $photoFile->getRealPath()),
                        'uploaded_by' => $request->user()?->id,
                    ]);

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

                    $isPrimary = false;
                }

                foreach ($existingPhotos as $existingPhoto) {
                    if ($existingPhoto->file) {
                        Storage::disk($existingPhoto->file->disk)->delete($existingPhoto->file->path);
                        $existingPhoto->file->delete();
                    }
                    $existingPhoto->delete();
                }
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            if ($storedPermitPath) {
                Storage::disk('local')->delete($storedPermitPath);
            }
            throw $e;
        }

        return redirect()->route('weapons.show', $weapon)->with('status', 'Arma actualizada.');
    }

    public function destroy(Weapon $weapon)
    {
        $weapon->delete();

        return redirect()->route('weapons.index')->with('status', 'Arma eliminada.');
    }

    private function ownershipOptions(): array
    {
        return [
            'company_owned' => 'Propiedad de la empresa',
            'leased' => 'Arrendada',
            'third_party' => 'Terceros',
        ];
    }

    private function generateInternalCode(): string
    {
        do {
            $code = 'ARM-' . Str::upper(Str::random(8));
        } while (Weapon::where('internal_code', $code)->exists());

        return $code;
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Weapon;
use App\Models\WeaponPhoto;
use App\Services\WeaponDocumentService;
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

        $weapons = $query->with([
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker',
        ])->orderByDesc('id')->paginate(15)->withQueryString();

        return view('weapons.index', compact('weapons'));
    }

    public function create()
    {
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.create', compact('ownershipTypes'));
    }

    public function store(Request $request, WeaponDocumentService $documentService)
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
            'permit_type' => ['required', 'in:porte,tenencia'],
            'permit_number' => ['nullable', 'string', 'max:100'],
            'permit_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'file', 'image', 'max:5120'],
            'permit_photo' => ['required', 'file', 'image', 'max:5120'],
        ]);

        if (empty($data['internal_code'])) {
            $data['internal_code'] = $this->generateInternalCode();
        }

        $photos = $request->file('photos', []);
        $photoOrder = array_keys(WeaponPhoto::DESCRIPTIONS);
        $permitPhoto = $request->file('permit_photo');
        $storedPaths = [];
        $storedPermitPath = null;
        $weapon = null;

        try {
            DB::transaction(function () use (&$weapon, $data, $photos, $photoOrder, $permitPhoto, $request, $documentService, &$storedPaths, &$storedPermitPath) {
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

                $documentService->syncPermitDocument($weapon);
                $documentService->syncRenewalDocument($weapon);

                if (!$photos) {
                    return;
                }

                foreach ($photoOrder as $index => $description) {
                    $photoFile = $photos[$index] ?? null;
                    if (!$photoFile) {
                        continue;
                    }

                    $existingPhoto = $weapon->photos()->with('file')
                        ->where('description', $description)
                        ->first();
                    if ($existingPhoto) {
                        if ($existingPhoto->file) {
                            Storage::disk($existingPhoto->file->disk)->delete($existingPhoto->file->path);
                            $existingPhoto->file->delete();
                        }
                        $existingPhoto->delete();
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
                        'description' => $description,
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
                            'description' => $photo->description,
                        ],
                    ]);
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
                $query->orderBy('id');
            },
            'photos.file',
            'documents' => function ($query) {
                $query->orderByDesc('is_permit')
                    ->orderByDesc('is_renewal')
                    ->orderByDesc('id');
            },
            'documents.file',
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker',
        ]);
        $ownershipTypes = $this->ownershipOptions();
        $responsibles = collect();
        $portfolioClients = collect();
        $posts = collect();
        $workers = collect();
        $transferRecipients = collect();

        if (request()->user()?->isAdmin()) {
            $responsibles = \App\Models\User::where('role', 'RESPONSABLE')->orderBy('name')->get();
            $portfolioClients = \App\Models\Client::orderBy('name')->get();
            $transferRecipients = $responsibles;
        } elseif (request()->user()?->isResponsible()) {
            $responsibles = collect([request()->user()]);
            $portfolioClients = request()->user()?->clients()->orderBy('name')->get() ?? collect();
            $transferRecipients = \App\Models\User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        }

        $activeClientId = $weapon->activeClientAssignment?->client_id;
        if ($activeClientId) {
            if (request()->user()?->isAdmin()) {
                $posts = \App\Models\Post::where('client_id', $activeClientId)->orderBy('name')->get();
                $workers = \App\Models\Worker::where('client_id', $activeClientId)->orderBy('name')->get();
            } elseif (request()->user()?->isResponsible()) {
                $inPortfolio = request()->user()?->clients()->whereKey($activeClientId)->exists() ?? false;
                if ($inPortfolio) {
                    $posts = \App\Models\Post::where('client_id', $activeClientId)->orderBy('name')->get();
                    $workers = \App\Models\Worker::where('client_id', $activeClientId)
                        ->where('responsible_user_id', request()->user()?->id)
                        ->orderBy('name')
                        ->get();
                }
            }
        }

        $photoOrder = array_keys(WeaponPhoto::DESCRIPTIONS);
        $weapon->setRelation(
            'photos',
            $weapon->photos
                ->sortBy(fn ($photo) => array_search($photo->description, $photoOrder, true) ?? PHP_INT_MAX)
                ->values()
        );

        return view('weapons.show', compact('weapon', 'ownershipTypes', 'responsibles', 'portfolioClients', 'posts', 'workers', 'transferRecipients'));
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

    public function updatePermitPhoto(Request $request, Weapon $weapon, WeaponDocumentService $documentService)
    {
        $this->authorize('update', $weapon);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $permitPhoto = $data['photo'];
        $storedPermitPath = $permitPhoto->store('weapons/' . $weapon->id . '/permits', 'local');

        try {
            DB::transaction(function () use ($request, $weapon, $permitPhoto, $storedPermitPath) {
                $storedFile = File::create([
                    'disk' => 'local',
                    'path' => $storedPermitPath,
                    'original_name' => $permitPhoto->getClientOriginalName(),
                    'mime_type' => $permitPhoto->getClientMimeType(),
                    'size' => $permitPhoto->getSize(),
                    'checksum' => hash_file('sha256', $permitPhoto->getRealPath()),
                    'uploaded_by' => $request->user()?->id,
                ]);

                $oldPermitFile = $weapon->permitFile;
                $weapon->update(['permit_file_id' => $storedFile->id]);

                if ($oldPermitFile) {
                    Storage::disk($oldPermitFile->disk)->delete($oldPermitFile->path);
                    $oldPermitFile->delete();
                }
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($storedPermitPath);
            throw $e;
        }

        $documentService->syncPermitDocument($weapon);
        $documentService->syncRenewalDocument($weapon);

        return response()->json(['ok' => true]);
    }

    public function edit(Weapon $weapon)
    {
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.edit', compact('weapon', 'ownershipTypes'));
    }

    public function update(Request $request, Weapon $weapon, WeaponDocumentService $documentService)
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
            'permit_type' => ['required', 'in:porte,tenencia'],
            'permit_number' => ['nullable', 'string', 'max:100'],
            'permit_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'file', 'image', 'max:5120'],
            'permit_photo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $photos = $request->file('photos', []);
        $photoOrder = array_keys(WeaponPhoto::DESCRIPTIONS);
        $permitPhoto = $request->file('permit_photo');
        $storedPaths = [];
        $storedPermitPath = null;

        try {
            DB::transaction(function () use ($data, $photos, $photoOrder, $permitPhoto, $request, $weapon, $documentService, &$storedPaths, &$storedPermitPath) {
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

                $documentService->syncPermitDocument($weapon);
                $documentService->syncRenewalDocument($weapon);

                foreach ($photoOrder as $index => $description) {
                    $photoFile = $photos[$index] ?? null;
                    if (!$photoFile) {
                        continue;
                    }

                    $existingPhoto = $weapon->photos()->with('file')
                        ->where('description', $description)
                        ->first();
                    if ($existingPhoto) {
                        if ($existingPhoto->file) {
                            Storage::disk($existingPhoto->file->disk)->delete($existingPhoto->file->path);
                            $existingPhoto->file->delete();
                        }
                        $existingPhoto->delete();
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
                        'description' => $description,
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
                            'description' => $photo->description,
                        ],
                    ]);
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


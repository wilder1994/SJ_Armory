<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Client;
use App\Models\Weapon;
use App\Models\WeaponPhoto;
use App\Services\WeaponDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $search = trim((string) $request->input('q', ''));

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query->whereHas('clientAssignments', function ($assignmentQuery) use ($user) {
                $assignmentQuery->where('responsible_user_id', $user->id)->where('is_active', true);
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('internal_code', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%')
                    ->orWhere('weapon_type', 'like', '%' . $search . '%')
                    ->orWhere('permit_type', 'like', '%' . $search . '%')
                    ->orWhere('permit_number', 'like', '%' . $search . '%')
                    ->orWhere('caliber', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhereHas('activeClientAssignment.client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('activeClientAssignment.responsible', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('activePostAssignment.post', function ($postQuery) use ($search) {
                        $postQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('activeWorkerAssignment.worker', function ($workerQuery) use ($search) {
                        $workerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('document', 'like', '%' . $search . '%');
                    });
            });
        }

        $weapons = $query->with([
            'activeClientAssignment.client',
            'activeClientAssignment.responsible',
            'activePostAssignment.post',
            'activeWorkerAssignment.worker',
            'documents',
        ])->orderByDesc('id')->paginate(50)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'tbody' => view('weapons.partials.index_rows', compact('weapons'))->render(),
                'pagination' => view('weapons.partials.index_pagination', compact('weapons'))->render(),
            ]);
        }

        return view('weapons.index', compact('weapons', 'search'));
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
            'capacity' => ['required', 'string', 'max:50'],
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

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'weapon_created',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => null,
            'after' => $weapon->only(['internal_code', 'serial_number', 'weapon_type', 'caliber', 'brand', 'capacity']),
        ]);

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
        $posts = collect();
        $workers = collect();
        $clientOptions = collect();

        if (request()->user()?->isAdmin()) {
            $responsibles = \App\Models\User::where('role', 'RESPONSABLE')->orderBy('name')->get();
            $clientOptions = Client::orderBy('name')->get();
        } elseif (request()->user()?->isResponsible()) {
            $responsibles = collect([request()->user()]);
            $clientOptions = request()->user()?->clients()->orderBy('name')->get() ?? collect();
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

        return view('weapons.show', compact('weapon', 'ownershipTypes', 'responsibles', 'posts', 'workers', 'clientOptions'));
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
            'capacity' => ['required', 'string', 'max:50'],
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

        $before = $weapon->only([
            'internal_code',
            'serial_number',
            'weapon_type',
            'caliber',
            'brand',
            'capacity',
            'permit_type',
            'permit_number',
            'permit_expires_at',
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

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'weapon_updated',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => $before,
            'after' => $weapon->only([
                'internal_code',
                'serial_number',
                'weapon_type',
                'caliber',
                'brand',
                'capacity',
                'permit_type',
                'permit_number',
                'permit_expires_at',
            ]),
        ]);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Arma actualizada.');
    }

    public function destroy(Weapon $weapon)
    {
        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'weapon_deleted',
            'auditable_type' => Weapon::class,
            'auditable_id' => $weapon->id,
            'before' => $weapon->only(['internal_code', 'serial_number', 'weapon_type', 'caliber', 'brand', 'capacity']),
            'after' => null,
        ]);

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
        $prefix = 'SJ-';
        $latestCode = Weapon::where('internal_code', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(internal_code, 4) AS UNSIGNED) DESC')
            ->value('internal_code');
        $lastNumber = $latestCode ? (int) preg_replace('/\D/', '', $latestCode) : 0;
        $code = sprintf('%s%04d', $prefix, $lastNumber + 1);

        return $code;
    }

    public function toggleImprint(Request $request, Weapon $weapon)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $month = now()->format('Y-m');
        $received = $request->boolean('received');

        if ($received) {
            $weapon->update([
                'imprint_month' => $month,
                'imprint_received_by' => $user->id,
                'imprint_received_at' => now(),
            ]);
        } else {
            $weapon->update([
                'imprint_month' => null,
                'imprint_received_by' => null,
                'imprint_received_at' => null,
            ]);
        }

        return back();
    }
}



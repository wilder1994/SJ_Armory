<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\File;
use App\Models\Vest;
use App\Models\VestPhoto;
use App\Services\VestPhotoService;
use App\Support\VestPhotoSlotPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class VestPhotoController extends Controller
{
    public function __construct(
        private readonly VestPhotoService $photoService,
    ) {
    }

    public function store(Request $request, Vest $vest)
    {
        $this->authorize('updatePhotos', $vest);

        $descriptions = array_keys(VestPhoto::DESCRIPTIONS);
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'description' => ['required', Rule::in($descriptions)],
        ]);

        try {
            $photo = $this->photoService->storeOrReplacePhoto(
                $vest,
                $data['description'],
                $data['photo'],
                $request->user()
            );
        } catch (Throwable $e) {
            throw $e;
        }

        if ($request->expectsJson()) {
            return VestPhotoSlotPayload::json(
                VestPhotoSlotPayload::forVestPhoto($photo->load('file'))
            );
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

        try {
            $photo = $this->photoService->storeOrReplacePhoto(
                $vest,
                $photo->description,
                $data['photo'],
                $request->user()
            );
        } catch (Throwable $e) {
            throw $e;
        }

        if ($request->expectsJson()) {
            return VestPhotoSlotPayload::json(
                VestPhotoSlotPayload::forVestPhoto($photo->fresh()->load('file'))
            );
        }

        return redirect()->route('vests.show', $vest)->with('status', __('Foto actualizada.'));
    }

    public function destroy(Request $request, Vest $vest, VestPhoto $photo)
    {
        $this->authorize('updatePhotos', $vest);

        if ($photo->vest_id !== $vest->id) {
            abort(404);
        }

        $description = $photo->description;
        $label = VestPhoto::DESCRIPTIONS[$description] ?? $description;

        $file = $photo->file;
        $photo->delete();

        if ($file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'delete_vest_photo',
            'auditable_type' => Vest::class,
            'auditable_id' => $vest->id,
            'before' => ['description' => $description],
            'after' => null,
        ]);

        if ($request->expectsJson()) {
            return VestPhotoSlotPayload::json(
                VestPhotoSlotPayload::emptySlot($description, $label)
            );
        }

        return redirect()->route('vests.show', $vest)->with('status', __('Foto eliminada.'));
    }
}

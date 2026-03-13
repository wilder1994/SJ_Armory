<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use App\Models\WeaponDocument;
use App\Services\WeaponDocumentService;
use Illuminate\Http\Request;

class AlertsController extends Controller
{
    public function documents(Request $request)
    {
        $this->authorizeAdmin();

        $days = (int)$request->input('days', 120);
        if (!in_array($days, [30, 60, 90, 120], true)) {
            $days = 120;
        }

        $today = now()->startOfDay();
        $until = now()->addDays($days)->endOfDay();

        $expired = WeaponDocument::with(['weapon', 'file'])
            ->where('is_renewal', true)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<=', $today)
            ->orderBy('valid_until')
            ->get();

        $expiring = WeaponDocument::with(['weapon', 'file'])
            ->where('is_renewal', true)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '>', $today)
            ->whereBetween('valid_until', [$today, $until])
            ->orderBy('valid_until')
            ->get();

        return view('alerts.documents', compact('expired', 'expiring', 'days'));
    }

    public function downloadBatch(Request $request, WeaponDocumentService $documentService)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'weapon_ids' => ['required', 'array', 'min:1'],
            'weapon_ids.*' => ['integer', 'distinct', 'exists:weapons,id'],
        ]);

        $weapons = Weapon::with([
            'photos.file',
            'permitFile',
            'documents.file',
        ])
            ->whereIn('id', $data['weapon_ids'])
            ->orderBy('internal_code')
            ->get();

        abort_if($weapons->isEmpty(), 422, 'Debe seleccionar al menos un arma.');

        $batch = $documentService->buildBatchDocument($weapons);

        return response()->download($batch['path'], $batch['file_name'])->deleteFileAfterSend(true);
    }

    private function authorizeAdmin(): void
    {
        if (!request()->user()?->isAdmin() && !request()->user()?->isAuditor()) {
            abort(403);
        }
    }
}


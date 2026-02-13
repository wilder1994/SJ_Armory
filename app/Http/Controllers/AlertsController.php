<?php

namespace App\Http\Controllers;

use App\Models\WeaponDocument;
use Illuminate\Http\Request;

class AlertsController extends Controller
{
    public function documents(Request $request)
    {
        $this->authorizeAdmin();

        $days = (int)$request->input('days', 30);
        if (!in_array($days, [30, 60, 90], true)) {
            $days = 30;
        }

        $today = now()->startOfDay();
        $until = now()->addDays($days)->endOfDay();

        $expired = WeaponDocument::with(['weapon', 'file'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', $today)
            ->orderBy('valid_until')
            ->get();

        $expiring = WeaponDocument::with(['weapon', 'file'])
            ->whereNotNull('valid_until')
            ->whereBetween('valid_until', [$today, $until])
            ->orderBy('valid_until')
            ->get();

        return view('alerts.documents', compact('expired', 'expiring', 'days'));
    }

    private function authorizeAdmin(): void
    {
        if (!request()->user()?->isAdmin() && !request()->user()?->isAuditor()) {
            abort(403);
        }
    }
}


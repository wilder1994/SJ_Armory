<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\WeaponDocument;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        return view('reports.index');
    }

    public function weaponsByClient(Request $request)
    {
        $this->authorizeAdmin();

        $clients = Client::orderBy('name')->get();
        $clientId = $request->integer('client_id');

        $query = Weapon::query()->with(['activeClientAssignment.client', 'activeClientAssignment.responsible']);
        if ($clientId) {
            $query->whereHas('clientAssignments', function ($assignmentQuery) use ($clientId) {
                $assignmentQuery->where('is_active', true)->where('client_id', $clientId);
            });
        } else {
            $query->whereHas('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true);
            });
        }

        $weapons = $query->orderBy('internal_code')->paginate(50)->withQueryString();

        return view('reports.weapons_by_client', compact('weapons', 'clients', 'clientId'));
    }

    public function weaponsWithoutDestination()
    {
        $this->authorizeAdmin();

        $weapons = Weapon::whereDoesntHave('clientAssignments', function ($assignmentQuery) {
            $assignmentQuery->where('is_active', true);
        })->orderBy('internal_code')->paginate(50)->withQueryString();

        return view('reports.weapons_without_destination', compact('weapons'));
    }

    public function history(Request $request)
    {
        $this->authorizeAdmin();

        $weaponId = $request->integer('weapon_id');
        $weapons = Weapon::orderBy('internal_code')->get();

        $weapon = null;
        $assignments = collect();
        $documents = collect();

        if ($weaponId) {
            $weapon = Weapon::find($weaponId);
            if ($weapon) {
                $assignments = WeaponClientAssignment::with(['client', 'assignedBy', 'responsible'])
                    ->where('weapon_id', $weaponId)
                    ->orderByDesc('start_at')
                    ->get();
                $documents = WeaponDocument::with('file')
                    ->where('weapon_id', $weaponId)
                    ->orderByDesc('created_at')
                    ->get();
            }
        }

        return view('reports.weapon_history', compact('weapons', 'weapon', 'assignments', 'documents'));
    }

    public function audit(Request $request)
    {
        $this->authorizeAdmin();

        $days = (int)$request->input('days', 30);
        if (!in_array($days, [30, 90], true)) {
            $days = 30;
        }

        $since = now()->subDays($days);
        $logs = AuditLog::with('user')
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('reports.audit', compact('logs', 'days'));
    }

    private function authorizeAdmin(): void
    {
        if (!request()->user()?->isAdmin()) {
            abort(403);
        }
    }
}

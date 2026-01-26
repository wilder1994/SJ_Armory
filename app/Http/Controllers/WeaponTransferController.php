<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponTransfer;
use App\Services\WeaponAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeaponTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status', WeaponTransfer::STATUS_PENDING);
        if (!in_array($status, [
            WeaponTransfer::STATUS_PENDING,
            WeaponTransfer::STATUS_ACCEPTED,
            WeaponTransfer::STATUS_REJECTED,
            WeaponTransfer::STATUS_CANCELLED,
        ], true)) {
            $status = WeaponTransfer::STATUS_PENDING;
        }

        $incomingQuery = WeaponTransfer::with(['weapon.activeClientAssignment.client', 'fromUser', 'newClient'])
            ->where('status', $status);

        if (!$user->isAdmin()) {
            $incomingQuery->where('to_user_id', $user->id);
        }

        if ($search !== '') {
            $incomingQuery->where(function ($builder) use ($search) {
                $builder->whereHas('weapon', function ($weaponQuery) use ($search) {
                    $weaponQuery->where('internal_code', 'like', '%' . $search . '%')
                        ->orWhere('serial_number', 'like', '%' . $search . '%');
                })->orWhereHas('weapon.activeClientAssignment.client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $incoming = $incomingQuery->orderByDesc('requested_at')->get();

        $outgoingQuery = WeaponTransfer::with(['weapon.activeClientAssignment.client', 'toUser', 'newClient'])
            ->where('requested_by', $user->id)
            ->where('status', $status);

        if ($search !== '') {
            $outgoingQuery->where(function ($builder) use ($search) {
                $builder->whereHas('weapon', function ($weaponQuery) use ($search) {
                    $weaponQuery->where('internal_code', 'like', '%' . $search . '%')
                        ->orWhere('serial_number', 'like', '%' . $search . '%');
                })->orWhereHas('weapon.activeClientAssignment.client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $outgoing = $outgoingQuery->orderByDesc('requested_at')->get();

        return view('transfers.index', compact('incoming', 'outgoing', 'search', 'status'));
    }

    public function store(Request $request, Weapon $weapon)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $activeAssignment = $weapon->activeClientAssignment()->first();
        if (!$activeAssignment) {
            abort(422, 'El arma no tiene destino operativo activo.');
        }

        $data = $request->validate([
            'to_user_id' => ['required', 'exists:users,id'],
            'new_client_id' => ['nullable', 'exists:clients,id'],
            'note' => ['nullable', 'string'],
        ]);

        $fromUserId = $activeAssignment->responsible_user_id;
        if (!$user->isAdmin() && $fromUserId !== $user->id) {
            abort(403);
        }

        $toUser = User::where('role', 'RESPONSABLE')->find($data['to_user_id']);
        if (!$toUser) {
            abort(422, 'El destinatario no es valido.');
        }

        if ($toUser->id === $fromUserId) {
            abort(422, 'El destinatario debe ser diferente.');
        }

        $newClientId = $data['new_client_id'] ?? null;
        if (!$user->isAdmin()) {
            $newClientId = null;
        }

        $requestedClientId = $newClientId ?: $activeAssignment->client_id;
        $inPortfolio = $toUser->clients()->whereKey($requestedClientId)->exists();
        if (!$inPortfolio) {
            abort(422, 'El cliente no pertenece a la cartera del destinatario.');
        }

        $transfer = WeaponTransfer::create([
            'weapon_id' => $weapon->id,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUser->id,
            'requested_by' => $user->id,
            'new_client_id' => $newClientId,
            'status' => WeaponTransfer::STATUS_PENDING,
            'requested_at' => now(),
            'note' => $data['note'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'transfer_requested',
            'auditable_type' => WeaponTransfer::class,
            'auditable_id' => $transfer->id,
            'before' => null,
            'after' => [
                'weapon_id' => $weapon->id,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUser->id,
                'new_client_id' => $newClientId,
            ],
        ]);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Transferencia enviada.');
    }

    public function accept(Request $request, WeaponTransfer $transfer, WeaponAssignmentService $service)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if ($transfer->status !== WeaponTransfer::STATUS_PENDING) {
            abort(422, 'La transferencia ya fue resuelta.');
        }

        if (!$user->isAdmin() && $transfer->to_user_id !== $user->id) {
            abort(403);
        }

        $transfer->load(['weapon', 'toUser']);
        $weapon = $transfer->weapon;
        $activeAssignment = $weapon->activeClientAssignment()->first();
        if (!$activeAssignment) {
            abort(422, 'El arma no tiene destino operativo activo.');
        }

        $clientId = $transfer->new_client_id ?? $activeAssignment->client_id;
        $inPortfolio = $transfer->toUser->clients()->whereKey($clientId)->exists();
        if (!$inPortfolio) {
            abort(422, 'El cliente no pertenece a la cartera del destinatario.');
        }

        DB::transaction(function () use ($transfer, $weapon, $service, $clientId, $user) {
            $service->assignClient(
                $weapon,
                $clientId,
                $transfer->toUser,
                $user,
                now()->toDateString(),
                $transfer->note
            );

            $transfer->update([
                'status' => WeaponTransfer::STATUS_ACCEPTED,
                'accepted_by' => $user->id,
                'answered_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'transfer_accepted',
                'auditable_type' => WeaponTransfer::class,
                'auditable_id' => $transfer->id,
                'before' => ['status' => WeaponTransfer::STATUS_PENDING],
                'after' => ['status' => WeaponTransfer::STATUS_ACCEPTED, 'client_id' => $clientId],
            ]);
        });

        return redirect()->route('transfers.index')->with('status', 'Transferencia aceptada.');
    }

    public function reject(Request $request, WeaponTransfer $transfer)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if ($transfer->status !== WeaponTransfer::STATUS_PENDING) {
            abort(422, 'La transferencia ya fue resuelta.');
        }

        if (!$user->isAdmin() && $transfer->to_user_id !== $user->id) {
            abort(403);
        }

        $transfer->update([
            'status' => WeaponTransfer::STATUS_REJECTED,
            'accepted_by' => $user->id,
            'answered_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'transfer_rejected',
            'auditable_type' => WeaponTransfer::class,
            'auditable_id' => $transfer->id,
            'before' => ['status' => WeaponTransfer::STATUS_PENDING],
            'after' => ['status' => WeaponTransfer::STATUS_REJECTED],
        ]);

        return redirect()->route('transfers.index')->with('status', 'Transferencia rechazada.');
    }
}

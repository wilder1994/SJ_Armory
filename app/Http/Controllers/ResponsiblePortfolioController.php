<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ResponsiblePortfolioController extends Controller
{
    public function index()
    {
        $user = request()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $responsibles = User::where('role', 'RESPONSABLE')->orderBy('name')->get();

        return view('portfolios.index', compact('responsibles'));
    }

    public function edit(User $user)
    {
        $authUser = request()->user();
        if (!$authUser || !$authUser->isAdmin()) {
            abort(403);
        }

        if (!$user->isResponsible()) {
            abort(404);
        }

        $clients = Client::orderBy('name')->get();
        $assigned = $user->clients()->pluck('clients.id')->all();

        return view('portfolios.edit', compact('user', 'clients', 'assigned'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = $request->user();
        if (!$authUser || !$authUser->isAdmin()) {
            abort(403);
        }

        if (!$user->isResponsible()) {
            abort(404);
        }

        $data = $request->validate([
            'clients' => ['array'],
            'clients.*' => ['exists:clients,id'],
        ]);

        $before = $user->clients()->pluck('clients.id')->all();
        $after = $data['clients'] ?? [];

        $user->clients()->sync($after);

        AuditLog::create([
            'user_id' => $authUser->id,
            'action' => 'portfolio_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => ['client_ids' => $before],
            'after' => ['client_ids' => $after],
        ]);

        return redirect()->route('portfolios.index')->with('status', 'Cartera actualizada.');
    }
}

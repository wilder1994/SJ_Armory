<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $action = $request->route()?->getActionMethod();

            if ($action === 'index') {
                if (!$user?->isAdmin() && !$user?->isResponsible()) {
                    abort(403);
                }

                return $next($request);
            }

            if (!$user?->isAdmin()) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Worker::with(['client', 'responsible']);
        $user = $request->user();
        $search = trim((string) $request->input('q', ''));
        $clientId = $request->integer('client_id');
        $role = $request->input('role');
        $responsibleId = $request->integer('responsible_user_id');

        if ($user?->isResponsible() && !$user?->isAdmin()) {
            $clientIds = $user->clients()->pluck('clients.id');
            $query->whereIn('client_id', $clientIds);
        }

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($responsibleId) {
            $query->where('responsible_user_id', $responsibleId);
        }

        $workers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        if ($user?->isResponsible() && !$user?->isAdmin()) {
            $clients = $user->clients()->orderBy('name')->get();
            $responsibles = collect([$user]);
        } else {
            $clients = Client::orderBy('name')->get();
            $responsibles = User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        }
        $roles = $this->roleOptions();

        return view('workers.index', compact('workers', 'clients', 'responsibles', 'roles', 'search', 'clientId', 'role', 'responsibleId'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $responsibles = User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        $roles = $this->roleOptions();

        return view('workers.create', compact('clients', 'responsibles', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:' . implode(',', array_keys($this->roleOptions()))],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $worker = Worker::create($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_created',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => null,
            'after' => $worker->only(['client_id', 'name', 'document', 'role', 'responsible_user_id']),
        ]);

        return redirect()->route('workers.index')->with('status', 'Trabajador creado.');
    }

    public function edit(Worker $worker)
    {
        $clients = Client::orderBy('name')->get();
        $responsibles = User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        $roles = $this->roleOptions();

        return view('workers.edit', compact('worker', 'clients', 'responsibles', 'roles'));
    }

    public function update(Request $request, Worker $worker)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:' . implode(',', array_keys($this->roleOptions()))],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $before = $worker->only(['client_id', 'name', 'document', 'role', 'responsible_user_id', 'notes']);
        $worker->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_updated',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => $before,
            'after' => $worker->only(['client_id', 'name', 'document', 'role', 'responsible_user_id', 'notes']),
        ]);

        return redirect()->route('workers.index')->with('status', 'Trabajador actualizado.');
    }

    public function destroy(Worker $worker)
    {
        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'worker_deleted',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => $worker->only(['client_id', 'name', 'document', 'role', 'responsible_user_id']),
            'after' => null,
        ]);

        $worker->delete();

        return redirect()->route('workers.index')->with('status', 'Trabajador eliminado.');
    }

    private function roleOptions(): array
    {
        return [
            Worker::ROLE_ESCOLTA => 'Escolta',
            Worker::ROLE_SUPERVISOR => 'Supervisor',
        ];
    }
}


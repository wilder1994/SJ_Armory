<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Position;
use App\Models\ResponsibilityLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isAdmin()) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with(['position', 'responsibilityLevel', 'clients'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $roles = $this->roleOptions();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = $this->roleOptions();
        $positions = Position::orderBy('name')->get();
        $responsibilityLevels = ResponsibilityLevel::whereIn('level', [1, 2])->orderBy('level')->get();

        return view('users.create', compact('roles', 'positions', 'responsibilityLevels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:' . implode(',', array_keys($this->roleOptions()))],
            'position_id' => ['nullable', 'exists:positions,id'],
            'responsibility_level_id' => ['nullable', 'exists:responsibility_levels,id'],
            'is_active' => ['required', 'boolean'],
            'cost_center' => ['nullable', 'string', 'max:100'],
        ]);

        if ($data['role'] === 'RESPONSABLE') {
            if (empty($data['responsibility_level_id'])) {
                return back()
                    ->withErrors(['responsibility_level_id' => 'Seleccione el nivel de responsabilidad para el responsable.'])
                    ->withInput();
            }
        } else {
            $data['responsibility_level_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => null,
            'after' => $user->only(['name', 'email', 'role', 'position_id', 'responsibility_level_id', 'is_active']),
        ]);

        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = $this->roleOptions();
        $positions = Position::orderBy('name')->get();
        $responsibilityLevels = ResponsibilityLevel::whereIn('level', [1, 2])->orderBy('level')->get();

        return view('users.edit', compact('user', 'roles', 'positions', 'responsibilityLevels'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:' . implode(',', array_keys($this->roleOptions()))],
            'position_id' => ['nullable', 'exists:positions,id'],
            'responsibility_level_id' => ['nullable', 'exists:responsibility_levels,id'],
            'is_active' => ['required', 'boolean'],
            'cost_center' => ['nullable', 'string', 'max:100'],
        ]);

        if ($data['role'] === 'RESPONSABLE') {
            if (empty($data['responsibility_level_id'])) {
                return back()
                    ->withErrors(['responsibility_level_id' => 'Seleccione el nivel de responsabilidad para el responsable.'])
                    ->withInput();
            }
        } else {
            $data['responsibility_level_id'] = null;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $before = $user->only(['name', 'email', 'role', 'position_id', 'responsibility_level_id', 'is_active']);
        $user->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $before,
            'after' => $user->only(['name', 'email', 'role', 'position_id', 'responsibility_level_id', 'is_active']),
        ]);

        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : !$user->is_active;

        $before = ['is_active' => $user->is_active];
        $user->update(['is_active' => $isActive]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_status_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $before,
            'after' => ['is_active' => $isActive],
        ]);

        return redirect()->route('users.index')->with('status', 'Estado actualizado.');
    }

    public function destroy(User $user)
    {
        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'user_deleted',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $user->only(['name', 'email', 'role', 'is_active']),
            'after' => null,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuario eliminado.');
    }

    private function roleOptions(): array
    {
        return [
            'ADMIN' => 'Administrador',
            'RESPONSABLE' => 'Responsable',
            'AUDITOR' => 'Auditor',
        ];
    }
}


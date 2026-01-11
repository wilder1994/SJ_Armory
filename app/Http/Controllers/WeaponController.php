<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Http\Request;

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
            $query->whereHas('custodies', function ($custodyQuery) use ($user) {
                $custodyQuery->where('custodian_user_id', $user->id)->where('is_active', true);
            });
        }

        if ($request->filled('operational_status')) {
            $query->where('operational_status', $request->string('operational_status')->toString());
        }

        $weapons = $query->with(['activeClientAssignment.client'])->orderByDesc('id')->paginate(15)->withQueryString();
        $statuses = $this->statusOptions();

        return view('weapons.index', compact('weapons', 'statuses'));
    }

    public function create()
    {
        $statuses = $this->statusOptions();
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.create', compact('statuses', 'ownershipTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internal_code' => ['required', 'string', 'max:100', 'unique:weapons,internal_code'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:weapons,serial_number'],
            'weapon_type' => ['required', 'string', 'max:100'],
            'caliber' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'operational_status' => ['required', 'in:' . implode(',', array_keys($this->statusOptions()))],
            'ownership_type' => ['required', 'in:' . implode(',', array_keys($this->ownershipOptions()))],
            'ownership_entity' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $weapon = Weapon::create($data);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Arma creada.');
    }

    public function show(Weapon $weapon)
    {
        $weapon->load([
            'photos' => function ($query) {
                $query->orderByDesc('is_primary')->orderBy('id');
            },
            'photos.file',
            'documents' => function ($query) {
                $query->orderByDesc('id');
            },
            'documents.file',
            'activeCustody.custodian',
            'activeClientAssignment.client',
        ]);
        $statuses = $this->statusOptions();
        $ownershipTypes = $this->ownershipOptions();
        $docTypes = $this->documentTypeOptions();
        $responsibles = [];
        $portfolioClients = [];

        if (request()->user()?->isAdmin()) {
            $responsibles = \App\Models\User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        }

        if (request()->user()?->isResponsible()) {
            $portfolioClients = request()->user()?->clients()->orderBy('name')->get() ?? collect();
        }

        return view('weapons.show', compact('weapon', 'statuses', 'ownershipTypes', 'docTypes', 'responsibles', 'portfolioClients'));
    }

    public function edit(Weapon $weapon)
    {
        $statuses = $this->statusOptions();
        $ownershipTypes = $this->ownershipOptions();

        return view('weapons.edit', compact('weapon', 'statuses', 'ownershipTypes'));
    }

    public function update(Request $request, Weapon $weapon)
    {
        $data = $request->validate([
            'internal_code' => ['required', 'string', 'max:100', 'unique:weapons,internal_code,' . $weapon->id],
            'serial_number' => ['required', 'string', 'max:100', 'unique:weapons,serial_number,' . $weapon->id],
            'weapon_type' => ['required', 'string', 'max:100'],
            'caliber' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'operational_status' => ['required', 'in:' . implode(',', array_keys($this->statusOptions()))],
            'ownership_type' => ['required', 'in:' . implode(',', array_keys($this->ownershipOptions()))],
            'ownership_entity' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $weapon->update($data);

        return redirect()->route('weapons.show', $weapon)->with('status', 'Arma actualizada.');
    }

    public function destroy(Weapon $weapon)
    {
        $weapon->delete();

        return redirect()->route('weapons.index')->with('status', 'Arma eliminada.');
    }

    private function statusOptions(): array
    {
        return [
            'in_armory' => 'En armería',
            'assigned' => 'Asignada',
            'in_transit' => 'En tránsito',
            'in_maintenance' => 'En mantenimiento',
            'seized_or_withdrawn' => 'Decomisada o retirada',
            'decommissioned' => 'Baja definitiva',
        ];
    }

    private function ownershipOptions(): array
    {
        return [
            'company_owned' => 'Propiedad de la empresa',
            'leased' => 'Arrendada',
            'third_party' => 'Terceros',
        ];
    }

    private function documentTypeOptions(): array
    {
        return [
            'ownership_support' => 'Soporte de propiedad',
            'permit_or_authorization' => 'Permiso o autorizacion',
            'revalidation' => 'Revalidacion',
            'maintenance_record' => 'Registro de mantenimiento',
            'seizure_or_withdrawal' => 'Acta de decomiso o retiro',
            'decommission_record' => 'Acta de baja',
            'other' => 'Otro',
        ];
    }
}

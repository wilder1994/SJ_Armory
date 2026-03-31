<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weapon extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'internal_code',
        'serial_number',
        'weapon_type',
        'caliber',
        'brand',
        'capacity',
        'ownership_type',
        'ownership_entity',
        'permit_type',
        'permit_number',
        'permit_expires_at',
        'permit_file_id',
        'notes',
        'imprint_month',
        'imprint_received_by',
        'imprint_received_at',
    ];

    protected $casts = [
        'permit_expires_at' => 'date',
    ];

    public function photos()
    {
        return $this->hasMany(WeaponPhoto::class);
    }

    public function documents()
    {
        return $this->hasMany(WeaponDocument::class);
    }

    public function incidents()
    {
        return $this->hasMany(WeaponIncident::class)->orderByDesc('event_at')->orderByDesc('id');
    }

    public function openIncidents()
    {
        return $this->hasMany(WeaponIncident::class)
            ->whereIn('status', [WeaponIncident::STATUS_OPEN, WeaponIncident::STATUS_IN_PROGRESS])
            ->orderByDesc('event_at')
            ->orderByDesc('id');
    }

    public function operationalBlockingIncidents()
    {
        return $this->hasMany(WeaponIncident::class)
            ->operationalBlockers()
            ->orderByDesc('event_at')
            ->orderByDesc('id');
    }

    public function clientAssignments()
    {
        return $this->hasMany(WeaponClientAssignment::class);
    }

    public function activeClientAssignment()
    {
        return $this->hasOne(WeaponClientAssignment::class)->where('is_active', true);
    }

    public function postAssignments()
    {
        return $this->hasMany(WeaponPostAssignment::class);
    }

    public function activePostAssignment()
    {
        return $this->hasOne(WeaponPostAssignment::class)->where('is_active', true);
    }

    public function workerAssignments()
    {
        return $this->hasMany(WeaponWorkerAssignment::class);
    }

    public function activeWorkerAssignment()
    {
        return $this->hasOne(WeaponWorkerAssignment::class)->where('is_active', true);
    }

    public function transfers()
    {
        return $this->hasMany(WeaponTransfer::class);
    }

    public function permitFile()
    {
        return $this->belongsTo(File::class, 'permit_file_id');
    }

    public function scopeOperationalInventory(Builder $query): Builder
    {
        return $query->whereDoesntHave('incidents', fn (Builder $incidentQuery) => $incidentQuery->operationalBlockers());
    }

    public function scopeNonOperationalInventory(Builder $query): Builder
    {
        return $query->whereHas('incidents', fn (Builder $incidentQuery) => $incidentQuery->operationalBlockers());
    }
}
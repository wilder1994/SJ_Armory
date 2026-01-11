<?php

namespace App\Models;

use App\Traits\Auditable;
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
        'model',
        'operational_status',
        'ownership_type',
        'ownership_entity',
        'permit_type',
        'permit_number',
        'permit_expires_at',
        'permit_file_id',
        'notes',
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

    public function custodies()
    {
        return $this->hasMany(WeaponCustody::class);
    }

    public function activeCustody()
    {
        return $this->hasOne(WeaponCustody::class)->where('is_active', true);
    }

    public function clientAssignments()
    {
        return $this->hasMany(WeaponClientAssignment::class);
    }

    public function activeClientAssignment()
    {
        return $this->hasOne(WeaponClientAssignment::class)->where('is_active', true);
    }

    public function permitFile()
    {
        return $this->belongsTo(File::class, 'permit_file_id');
    }
}

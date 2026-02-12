<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'weapon_id',
        'file_id',
        'document_name',
        'document_number',
        'permit_kind',
        'valid_until',
        'observations',
        'status',
        'is_permit',
        'is_renewal',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'is_permit' => 'boolean',
        'is_renewal' => 'boolean',
    ];

    public function weapon()
    {
        return $this->belongsTo(Weapon::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}


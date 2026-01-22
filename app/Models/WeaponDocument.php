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
        'valid_until',
        'observations',
    ];

    protected $casts = [
        'valid_until' => 'date',
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

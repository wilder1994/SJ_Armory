<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponPhoto extends Model
{
    use HasFactory;

    public const DESCRIPTIONS = [
        'lado_derecho' => 'Lado derecho',
        'lado_izquierdo' => 'Lado izquierdo',
        'canon_disparador_marca' => 'Cañon, disparador, marca',
        'serie' => 'Serie',
        'aseo' => 'Aseo',
    ];

    protected $fillable = [
        'weapon_id',
        'file_id',
        'description',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VestPhoto extends Model
{
    use HasFactory;

    public const DESCRIPTIONS = [
        'vista_completa_1' => 'Vista completa 1',
        'vista_completa_2' => 'Vista completa 2',
        'placa_serie_1' => 'Placa serie 1',
        'placa_serie_2' => 'Placa serie 2',
    ];

    protected $fillable = [
        'vest_id',
        'file_id',
        'description',
    ];

    public function vest()
    {
        return $this->belongsTo(Vest::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}

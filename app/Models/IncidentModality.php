<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentModality extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_type_id',
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function incidentType()
    {
        return $this->belongsTo(IncidentType::class);
    }

    public function incidents()
    {
        return $this->hasMany(WeaponIncident::class, 'incident_modality_id');
    }
}

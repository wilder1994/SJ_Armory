<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponCustody extends Model
{
    use HasFactory;

    protected $fillable = [
        'weapon_id',
        'custodian_user_id',
        'start_at',
        'end_at',
        'is_active',
        'assigned_by',
        'reason',
        'support_file_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function weapon()
    {
        return $this->belongsTo(Weapon::class);
    }

    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function supportFile()
    {
        return $this->belongsTo(File::class, 'support_file_id');
    }
}

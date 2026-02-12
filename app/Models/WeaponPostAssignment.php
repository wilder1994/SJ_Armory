<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponPostAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'weapon_id',
        'post_id',
        'assigned_by',
        'start_at',
        'end_at',
        'is_active',
        'reason',
        'ammo_count',
        'provider_count',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function weapon()
    {
        return $this->belongsTo(Weapon::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}


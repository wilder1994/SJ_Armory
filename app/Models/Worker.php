<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    public const ROLE_ESCOLTA = 'ESCOLTA';
    public const ROLE_SUPERVISOR = 'SUPERVISOR';

    protected $fillable = [
        'client_id',
        'name',
        'document',
        'role',
        'responsible_user_id',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function assignments()
    {
        return $this->hasMany(WeaponWorkerAssignment::class);
    }
}

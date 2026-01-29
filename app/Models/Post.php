<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'address',
        'city',
        'department',
        'latitude',
        'longitude',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignments()
    {
        return $this->hasMany(WeaponPostAssignment::class);
    }
}

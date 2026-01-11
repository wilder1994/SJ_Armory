<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nit',
        'legal_representative',
        'contact_name',
        'email',
        'address',
        'neighborhood',
        'city',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_clients')->withTimestamps();
    }
}

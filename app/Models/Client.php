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
        'department',
        'latitude',
        'longitude',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_clients')->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }
}


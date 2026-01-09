<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsibilityLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

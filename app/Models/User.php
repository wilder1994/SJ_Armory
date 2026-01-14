<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position_id',
        'responsibility_level_id',
        'is_active',
        'cost_center',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function responsibilityLevel()
    {
        return $this->belongsTo(ResponsibilityLevel::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'user_clients')->withTimestamps();
    }

    public function custodies()
    {
        return $this->hasMany(WeaponCustody::class, 'custodian_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isResponsible(): bool
    {
        return $this->role === 'RESPONSABLE';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'AUDITOR';
    }
}

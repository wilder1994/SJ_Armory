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

    public const ROLE_ADMIN = 'ADMIN';

    public const ROLE_RESPONSABLE = 'RESPONSABLE';

    public const ROLE_AUDITOR = 'AUDITOR';

    public const ROLE_ALMACEN = 'ALMACEN';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
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
        'must_change_password' => 'boolean',
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

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isResponsible(): bool
    {
        return $this->role === self::ROLE_RESPONSABLE;
    }

    public function isAuditor(): bool
    {
        return $this->role === self::ROLE_AUDITOR;
    }

    public function isAlmacen(): bool
    {
        return $this->role === self::ROLE_ALMACEN;
    }

    public function canAccessVestModule(): bool
    {
        return $this->isAdmin()
            || $this->isResponsible()
            || $this->isAuditor()
            || $this->isAlmacen();
    }

    public function hasGlobalVestScope(): bool
    {
        return $this->isAdmin() || $this->isAuditor() || $this->isAlmacen();
    }

    public function canManageAllVests(): bool
    {
        return $this->isAdmin() || $this->isAlmacen();
    }

    public function responsibilityLevelNumber(): ?int
    {
        if ($this->responsibilityLevel) {
            return (int) $this->responsibilityLevel->level;
        }

        if (!$this->responsibility_level_id) {
            return null;
        }

        return (int) optional($this->responsibilityLevel()->first())->level;
    }

    public function isResponsibleLevelOne(): bool
    {
        return $this->isResponsible() && $this->responsibilityLevelNumber() === 1;
    }

    public function hasClientInPortfolio(int $clientId): bool
    {
        return $this->clients()->whereKey($clientId)->exists();
    }

    /**
     * Responsable válido para custodia/taller en un cliente: nivel 1 con cartera o ADMIN con cartera.
     */
    public function isCustodyResponsibleForClient(int $clientId): bool
    {
        if ($clientId <= 0 || ! $this->hasClientInPortfolio($clientId)) {
            return false;
        }

        return $this->isResponsibleLevelOne() || $this->isAdmin();
    }

    public function isResponsibleReadOnly(): bool
    {
        return $this->isResponsible() && $this->responsibilityLevelNumber() === 2;
    }

    public function isReadOnlyOperator(): bool
    {
        return $this->isAuditor() || $this->isResponsibleReadOnly();
    }
}


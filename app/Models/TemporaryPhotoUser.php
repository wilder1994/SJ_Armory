<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemporaryPhotoUser extends Model
{
    protected $fillable = [
        'owner_responsible_user_id',
        'created_by_user_id',
        'name',
        'email',
        'is_active',
        'deactivated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    public function ownerResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_responsible_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function grants(): HasMany
    {
        return $this->hasMany(TemporaryPhotoAccessGrant::class);
    }

    public function stagingPhotos(): HasMany
    {
        return $this->hasMany(WeaponPhotoStaging::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('deactivated_at');
    }
}

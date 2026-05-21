<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemporaryPhotoAccessGrant extends Model
{
    protected $fillable = [
        'temporary_photo_user_id',
        'created_by_user_id',
        'access_code_hash',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function temporaryPhotoUser(): BelongsTo
    {
        return $this->belongsTo(TemporaryPhotoUser::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function weapons(): HasMany
    {
        return $this->hasMany(TemporaryPhotoAccessWeapon::class);
    }

    public function isCurrentlyValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        return $this->expires_at->isFuture();
    }
}

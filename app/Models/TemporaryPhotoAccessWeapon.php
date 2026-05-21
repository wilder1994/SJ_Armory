<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryPhotoAccessWeapon extends Model
{
    protected $fillable = [
        'temporary_photo_access_grant_id',
        'weapon_id',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(TemporaryPhotoAccessGrant::class, 'temporary_photo_access_grant_id');
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class);
    }
}

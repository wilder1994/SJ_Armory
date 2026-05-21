<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPhotoStaging extends Model
{
    protected $table = 'weapon_photo_staging';

    protected $fillable = [
        'temporary_photo_user_id',
        'weapon_id',
        'description',
        'file_id',
    ];

    public function temporaryPhotoUser(): BelongsTo
    {
        return $this->belongsTo(TemporaryPhotoUser::class);
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}

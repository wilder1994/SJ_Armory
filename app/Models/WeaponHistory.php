<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponHistory extends Model
{
    public const KIND_CREATED = 'created';

    public const KIND_NOTE = 'note';

    public const KIND_UPDATE = 'update';

    public const KIND_DESTINATION = 'destination';

    public const KIND_INTERNAL = 'internal';

    public const KIND_INCIDENT = 'incident';

    public const KIND_TRANSFER = 'transfer';

    public const KIND_DOCUMENT = 'document';

    public const KIND_PHOTOS = 'photos';

    protected $fillable = [
        'weapon_id',
        'user_id',
        'kind',
        'body',
    ];

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function kindLabels(): array
    {
        return [
            self::KIND_CREATED => __('Creación'),
            self::KIND_NOTE => __('Nota'),
            self::KIND_UPDATE => __('Actualización'),
            self::KIND_DESTINATION => __('Destino operativo'),
            self::KIND_INTERNAL => __('Asignación interna'),
            self::KIND_INCIDENT => __('Novedad'),
            self::KIND_TRANSFER => __('Transferencia'),
            self::KIND_DOCUMENT => __('Documento'),
            self::KIND_PHOTOS => __('Fotografías'),
        ];
    }

    public function kindLabel(): string
    {
        return self::kindLabels()[$this->kind] ?? $this->kind;
    }
}

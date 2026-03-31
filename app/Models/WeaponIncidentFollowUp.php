<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponIncidentFollowUp extends Model
{
    use HasFactory;

    public const ENTRY_NOTE = 'note';
    public const ENTRY_STATUS = 'status_change';
    public const ENTRY_CONTACT = 'contact';
    public const ENTRY_DOCUMENT = 'document_review';
    public const ENTRY_RECOVERY = 'recovery';
    public const ENTRY_REINTEGRATION = 'reintegration';
    public const ENTRY_REOPEN = 'reopen';
    public const ENTRY_CLOSURE = 'closure';

    protected $fillable = [
        'weapon_incident_id',
        'user_id',
        'entry_type',
        'message',
        'follow_up_at',
        'meta',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
        'meta' => 'array',
    ];

    public static function entryTypeOptions(): array
    {
        return [
            self::ENTRY_NOTE => 'Nota',
            self::ENTRY_STATUS => 'Cambio de estado',
            self::ENTRY_CONTACT => 'Seguimiento',
            self::ENTRY_DOCUMENT => 'Revision documental',
            self::ENTRY_RECOVERY => 'Recuperacion',
            self::ENTRY_REINTEGRATION => 'Reintegracion',
            self::ENTRY_REOPEN => 'Reapertura',
            self::ENTRY_CLOSURE => 'Cierre',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(WeaponIncident::class, 'weapon_incident_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entryTypeLabel(): string
    {
        return self::entryTypeOptions()[$this->entry_type] ?? $this->entry_type;
    }
}

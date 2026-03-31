<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponIncident extends Model
{
    use HasFactory;
    use Auditable;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'weapon_id',
        'incident_type_id',
        'incident_modality_id',
        'status',
        'observation',
        'note',
        'event_at',
        'reported_at',
        'reported_by',
        'source_document_id',
        'attachment_file_id',
        'resolved_at',
        'resolved_by',
        'resolution_note',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function weapon()
    {
        return $this->belongsTo(Weapon::class);
    }

    public function type()
    {
        return $this->belongsTo(IncidentType::class, 'incident_type_id');
    }

    public function modality()
    {
        return $this->belongsTo(IncidentModality::class, 'incident_modality_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function sourceDocument()
    {
        return $this->belongsTo(WeaponDocument::class, 'source_document_id');
    }

    public function attachmentFile()
    {
        return $this->belongsTo(File::class, 'attachment_file_id');
    }

    public function updates()
    {
        return $this->hasMany(WeaponIncidentUpdate::class)
            ->orderByDesc('happened_at')
            ->orderByDesc('id');
    }

    public function latestUpdate()
    {
        return $this->hasOne(WeaponIncidentUpdate::class)->latestOfMany('happened_at');
    }

    public function followUps()
    {
        return $this->hasMany(WeaponIncidentFollowUp::class)
            ->orderByDesc('follow_up_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function latestFollowUp()
    {
        return $this->hasOne(WeaponIncidentFollowUp::class)->latestOfMany('follow_up_at');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeOperationalBlockers(Builder $query): Builder
    {
        return $query
            ->whereHas('type', fn (Builder $typeQuery) => $typeQuery->where('blocks_operation', true))
            ->where(function (Builder $statusQuery) {
                $statusQuery->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS])
                    ->orWhere(function (Builder $terminalQuery) {
                        $terminalQuery
                            ->where('status', '!=', self::STATUS_CANCELLED)
                            ->whereHas('type', fn (Builder $typeQuery) => $typeQuery->where('persists_operational_block', true));
                    });
            });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'Abierta',
            self::STATUS_IN_PROGRESS => 'En proceso',
            self::STATUS_RESOLVED => 'Resuelta',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    public static function initialStatusOptions(): array
    {
        return [
            self::STATUS_OPEN => self::statusOptions()[self::STATUS_OPEN],
            self::STATUS_IN_PROGRESS => self::statusOptions()[self::STATUS_IN_PROGRESS],
        ];
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS], true);
    }

    public function blocksOperationalAvailability(): bool
    {
        if (!$this->type?->blocks_operation) {
            return false;
        }

        if ($this->isOpen()) {
            return true;
        }

        return $this->status !== self::STATUS_CANCELLED && (bool) $this->type?->persists_operational_block;
    }

    public function latestActivityAt()
    {
        $latestUpdateAt = $this->latestUpdate?->happened_at;

        if (!$latestUpdateAt) {
            return $this->event_at;
        }

        if (!$this->event_at) {
            return $latestUpdateAt;
        }

        return $latestUpdateAt->greaterThan($this->event_at)
            ? $latestUpdateAt
            : $this->event_at;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'worker_id',
        'post_id',
        'serial_number',
        'brand',
        'batch',
        'size',
        'manufactured_at',
        'expires_at',
        'device_responsible',
        'notes',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function photos()
    {
        return $this->hasMany(VestPhoto::class);
    }

    public function isAssigned(): bool
    {
        return $this->worker_id !== null;
    }

    public function scopeForUserPortfolio(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isAuditor()) {
            return $query;
        }

        if ($user->isResponsible()) {
            $clientIds = $user->clients()->pluck('clients.id');

            return $query->whereIn('client_id', $clientIds);
        }

        return $query->whereRaw('0 = 1');
    }

    public function photosCount(): int
    {
        return $this->photos()->count();
    }

    public function hasCompletePhotos(): bool
    {
        return $this->photosCount() >= count(VestPhoto::DESCRIPTIONS);
    }

    public function displayDeviceResponsible(): ?string
    {
        if (filled($this->device_responsible)) {
            return $this->device_responsible;
        }

        return self::clientDeviceResponsibleName($this->client_id);
    }

    public static function clientDeviceResponsibleName(?int $clientId): ?string
    {
        if (! $clientId) {
            return null;
        }

        return User::query()
            ->whereIn('role', ['RESPONSABLE', 'ADMIN'])
            ->whereHas('clients', fn ($query) => $query->whereKey($clientId))
            ->orderByRaw("CASE WHEN role = 'RESPONSABLE' THEN 0 WHEN role = 'ADMIN' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->value('name');
    }
}

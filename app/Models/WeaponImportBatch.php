<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeaponImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'uploaded_by',
        'executed_by',
        'status',
        'source_name',
        'total_rows',
        'create_count',
        'update_count',
        'no_change_count',
        'error_count',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function executedBy()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function rows()
    {
        return $this->hasMany(WeaponImportRow::class, 'batch_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isExecuted(): bool
    {
        return $this->status === 'executed';
    }

    public function hasErrors(): bool
    {
        return (int) $this->error_count > 0;
    }
}

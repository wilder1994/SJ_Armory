<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected $auditOriginal = null;

    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAttributes());
        });

        static::updating(function ($model) {
            $model->auditOriginal = $model->getOriginal();
        });

        static::updated(function ($model) {
            $model->writeAudit('updated', $model->auditOriginal, $model->getAttributes());
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->getOriginal(), null);
        });
    }

    protected function writeAudit(string $action, $before, $after): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'before' => $before,
            'after' => $after,
        ]);
    }
}

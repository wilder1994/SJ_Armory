<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAttributes());
        });

        static::updating(function ($model) {
            $model->audit_original = $model->getOriginal();
        });

        static::updated(function ($model) {
            $model->writeAudit('updated', $model->audit_original ?? null, $model->getAttributes());
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

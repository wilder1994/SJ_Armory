<?php

namespace App\Support;

use App\Models\Vest;
use Carbon\Carbon;

class VestAlert
{
    public const ALERT_ALL = 'all';
    public const ALERT_VIGENT = 'vigent';
    public const ALERT_PREVENTIVE = 'preventive';
    public const ALERT_CRITICAL = 'critical';
    public const ALERT_EXPIRED = 'expired';
    public const ALERT_UNASSIGNED = 'unassigned';

    public const ALERT_LABELS = [
        self::ALERT_ALL => 'Todos',
        self::ALERT_VIGENT => 'Vigentes',
        self::ALERT_PREVENTIVE => 'Preventivos',
        self::ALERT_CRITICAL => 'Críticos',
        self::ALERT_EXPIRED => 'Vencidos',
        self::ALERT_UNASSIGNED => 'Sin asignar',
    ];

    public static function forVest(Vest $vest, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->startOfDay();
        $expiresAt = $vest->expires_at?->copy()->startOfDay();

        if ($expiresAt === null) {
            return [
                'key' => self::ALERT_ALL,
                'state' => 'Sin fecha',
                'days_remaining' => null,
                'row_class' => '',
                'text_class' => 'text-slate-600',
                'badge_class' => 'bg-slate-100 text-slate-700',
            ];
        }

        $daysRemaining = $asOf->diffInDays($expiresAt, false);

        if ($daysRemaining < 0) {
            return [
                'key' => self::ALERT_EXPIRED,
                'state' => 'Vencido',
                'days_remaining' => $daysRemaining,
                'row_class' => 'bg-red-50',
                'text_class' => 'text-red-700',
                'badge_class' => 'bg-red-100 text-red-800',
            ];
        }

        if ($daysRemaining < 180) {
            return [
                'key' => self::ALERT_CRITICAL,
                'state' => 'Crítico',
                'days_remaining' => $daysRemaining,
                'row_class' => 'bg-red-50',
                'text_class' => 'text-red-700',
                'badge_class' => 'bg-red-100 text-red-800',
            ];
        }

        if ($daysRemaining <= 365) {
            return [
                'key' => self::ALERT_PREVENTIVE,
                'state' => 'Preventivo',
                'days_remaining' => $daysRemaining,
                'row_class' => 'bg-amber-50',
                'text_class' => 'text-amber-800',
                'badge_class' => 'bg-amber-100 text-amber-900',
            ];
        }

        return [
            'key' => self::ALERT_VIGENT,
            'state' => 'Vigente',
            'days_remaining' => $daysRemaining,
            'row_class' => 'bg-emerald-50',
            'text_class' => 'text-emerald-800',
            'badge_class' => 'bg-emerald-100 text-emerald-900',
        ];
    }

    public static function normalizeAlertFilter(?string $alert): ?string
    {
        $alert = strtolower(trim((string) $alert));

        return in_array($alert, [
            self::ALERT_ALL,
            self::ALERT_VIGENT,
            self::ALERT_PREVENTIVE,
            self::ALERT_CRITICAL,
            self::ALERT_EXPIRED,
            self::ALERT_UNASSIGNED,
        ], true) ? $alert : null;
    }
}

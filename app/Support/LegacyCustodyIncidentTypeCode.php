<?php

namespace App\Support;

final class LegacyCustodyIncidentTypeCode
{
    public const EN_MANTENIMIENTO = 'en_mantenimiento';

    public const PARA_MANTENIMIENTO = 'para_mantenimiento';

    public const EN_ARMERILLO = 'en_armerillo';

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return [
            self::EN_MANTENIMIENTO,
            self::PARA_MANTENIMIENTO,
            self::EN_ARMERILLO,
        ];
    }

    public static function isLegacy(?string $code): bool
    {
        return $code !== null && in_array($code, self::codes(), true);
    }
}

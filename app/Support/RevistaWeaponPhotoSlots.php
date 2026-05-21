<?php

namespace App\Support;

class RevistaWeaponPhotoSlots
{
    public const DESCRIPTIONS = [
        'lado_derecho' => 'Lado derecho',
        'lado_izquierdo' => 'Lado izquierdo',
        'canon_disparador_marca' => 'Cañón, disparador, marca',
        'serie' => 'Serie',
    ];

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::DESCRIPTIONS);
    }

    public static function requiredCount(): int
    {
        return count(self::DESCRIPTIONS);
    }
}

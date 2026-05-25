<?php

namespace App\Support;

use App\Models\Weapon;

/**
 * Estilos de fila en exportación XLSX de armamento (índices cellXfs del generador).
 */
class WeaponPhotoExportHighlight
{
    public const STYLE_ORANGE = 2;

    public const STYLE_YELLOW = 3;

    public const STYLE_GREEN = 4;

    /**
     * Sin color: 0 fotos o 1–3 (falta alguna de las 4 base).
     * Naranja: 4 base. Amarillo: 4 base + impronta. Verde: 4 base + impronta + permiso del arma.
     */
    public static function rowStyleFor(Weapon $weapon): ?int
    {
        $present = $weapon->relationLoaded('photos')
            ? $weapon->photos->pluck('description')->unique()->all()
            : $weapon->photos()->pluck('description')->all();

        $has = static fn (string $key): bool => in_array($key, $present, true);

        $hasAllBase = collect(RevistaWeaponPhotoSlots::keys())->every($has);
        if (! $hasAllBase) {
            return null;
        }

        if ($weapon->permit_file_id && $has('impronta')) {
            return self::STYLE_GREEN;
        }

        if ($has('impronta')) {
            return self::STYLE_YELLOW;
        }

        return self::STYLE_ORANGE;
    }

    /**
     * Filas para la hoja «Criterios de color» del XLSX (muestra + significado).
     *
     * @return list<array{style: int|null, sample: string, meaning: string}>
     */
    public static function legendSheetRows(): array
    {
        return [
            [
                'style' => null,
                'sample' => 'Sin resaltar',
                'meaning' => 'Sin fotos, de 1 a 3 fotos, o faltan alguna de las cuatro base (lado derecho, lado izquierdo, cañón/disparador/marca, serie).',
            ],
            [
                'style' => self::STYLE_ORANGE,
                'sample' => 'Naranja',
                'meaning' => 'Tiene las cuatro fotos base del arma.',
            ],
            [
                'style' => self::STYLE_YELLOW,
                'sample' => 'Amarillo',
                'meaning' => 'Tiene las cuatro fotos base y la foto de impronta.',
            ],
            [
                'style' => self::STYLE_GREEN,
                'sample' => 'Verde',
                'meaning' => 'Tiene las cuatro fotos base, impronta y la foto del permiso cargada en la ficha del arma.',
            ],
        ];
    }
}

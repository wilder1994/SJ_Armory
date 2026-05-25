<?php

namespace Tests\Unit;

use App\Models\Weapon;
use App\Models\WeaponPhoto;
use App\Support\WeaponPhotoExportHighlight;
use PHPUnit\Framework\TestCase;

class WeaponPhotoExportHighlightTest extends TestCase
{
    public function test_no_highlight_when_incomplete_base_photos(): void
    {
        $weapon = $this->weaponWithPhotos(['lado_derecho']);

        $this->assertNull(WeaponPhotoExportHighlight::rowStyleFor($weapon));
    }

    public function test_orange_when_four_base_photos_only(): void
    {
        $weapon = $this->weaponWithPhotos([
            'lado_derecho',
            'lado_izquierdo',
            'canon_disparador_marca',
            'serie',
        ]);

        $this->assertSame(WeaponPhotoExportHighlight::STYLE_ORANGE, WeaponPhotoExportHighlight::rowStyleFor($weapon));
    }

    public function test_yellow_when_base_plus_impronta(): void
    {
        $weapon = $this->weaponWithPhotos([
            'lado_derecho',
            'lado_izquierdo',
            'canon_disparador_marca',
            'serie',
            'impronta',
        ]);

        $this->assertSame(WeaponPhotoExportHighlight::STYLE_YELLOW, WeaponPhotoExportHighlight::rowStyleFor($weapon));
    }

    public function test_green_when_base_impronta_and_permit_file(): void
    {
        $weapon = $this->weaponWithPhotos([
            'lado_derecho',
            'lado_izquierdo',
            'canon_disparador_marca',
            'serie',
            'impronta',
        ], permitFileId: 99);

        $this->assertSame(WeaponPhotoExportHighlight::STYLE_GREEN, WeaponPhotoExportHighlight::rowStyleFor($weapon));
    }

    public function test_no_green_without_impronta_even_with_permit(): void
    {
        $weapon = $this->weaponWithPhotos([
            'lado_derecho',
            'lado_izquierdo',
            'canon_disparador_marca',
            'serie',
        ], permitFileId: 99);

        $this->assertSame(WeaponPhotoExportHighlight::STYLE_ORANGE, WeaponPhotoExportHighlight::rowStyleFor($weapon));
    }

    public function test_legend_sheet_has_four_criteria_rows(): void
    {
        $rows = WeaponPhotoExportHighlight::legendSheetRows();

        $this->assertCount(4, $rows);
        $this->assertNull($rows[0]['style']);
        $this->assertSame(WeaponPhotoExportHighlight::STYLE_ORANGE, $rows[1]['style']);
        $this->assertSame(WeaponPhotoExportHighlight::STYLE_YELLOW, $rows[2]['style']);
        $this->assertSame(WeaponPhotoExportHighlight::STYLE_GREEN, $rows[3]['style']);
    }

    /**
     * @param  list<string>  $descriptions
     */
    private function weaponWithPhotos(array $descriptions, ?int $permitFileId = null): Weapon
    {
        $weapon = new Weapon(['permit_file_id' => $permitFileId]);
        $weapon->setRelation(
            'photos',
            collect($descriptions)->map(fn (string $description) => new WeaponPhoto(['description' => $description])),
        );

        return $weapon;
    }
}

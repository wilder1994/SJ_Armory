<?php

namespace App\Services\Imports;

use App\Support\SimpleSpreadsheetExporter;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VestImportTemplateExporter
{
    public const TEMPLATE_PATH = 'resources/templates/Chalecos.xlsx';

    public const DOWNLOAD_FILENAME = 'formato-carga-chalecos.xlsx';

    public const INSTRUCTION_SHEET_NAME = 'Instructivo';

    public function __construct(
        private readonly SimpleSpreadsheetExporter $spreadsheetExporter,
    ) {}

    public function stream(): StreamedResponse
    {
        $sourcePath = base_path(self::TEMPLATE_PATH);
        if (! is_file($sourcePath)) {
            throw new RuntimeException('Plantilla de carga de chalecos no encontrada.');
        }

        return response()->streamDownload(function () use ($sourcePath) {
            $temporaryPath = tempnam(sys_get_temp_dir(), 'vest-import-template-');
            if ($temporaryPath === false || ! copy($sourcePath, $temporaryPath)) {
                throw new RuntimeException('No se pudo preparar la plantilla de chalecos.');
            }

            $this->spreadsheetExporter->appendInstructionSheet(
                $temporaryPath,
                self::INSTRUCTION_SHEET_NAME,
                ['Columna', 'Obligatorio', 'Formato', 'Descripción'],
                VestImportProcessor::templateInstructions(),
                [28, 14, 20, 72],
            );

            $handle = fopen($temporaryPath, 'rb');
            fpassthru($handle);
            fclose($handle);
            @unlink($temporaryPath);
        }, self::DOWNLOAD_FILENAME, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

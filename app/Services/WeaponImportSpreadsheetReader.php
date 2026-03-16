<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class WeaponImportSpreadsheetReader
{
    private const SPREADSHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const PACKAGE_REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /**
     * @return array{headers: array<int, string>, rows: array<int, array{row_number:int, cells: array<int, string>}>}
     */
    public function read(string $absolutePath, ?string $extension = null): array
    {
        $extension = strtolower((string) ($extension ?: pathinfo($absolutePath, PATHINFO_EXTENSION)));

        return match ($extension) {
            'xlsx' => $this->readXlsx($absolutePath),
            'csv', 'txt' => $this->readCsv($absolutePath),
            default => throw new RuntimeException('Formato de archivo no soportado.'),
        };
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array{row_number:int, cells: array<int, string>}>}
     */
    private function readCsv(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'rb');
        if (!$handle) {
            throw new RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $headers = [];
        $rows = [];
        $lineNumber = 0;

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $lineNumber++;
                $cells = array_map(fn ($value) => $this->sanitizeCell((string) $value), $row);

                if ($headers === []) {
                    if ($this->isEmptyRow($cells)) {
                        continue;
                    }

                    $headers = $cells;
                    continue;
                }

                if ($this->isEmptyRow($cells)) {
                    continue;
                }

                $rows[] = [
                    'row_number' => $lineNumber,
                    'cells' => $cells,
                ];
            }
        } finally {
            fclose($handle);
        }

        if ($headers === []) {
            throw new RuntimeException('El archivo no contiene encabezados.');
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array{row_number:int, cells: array<int, string>}>}
     */
    private function readXlsx(string $absolutePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo XLSX.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetPath = $this->resolveFirstWorksheetPath($zip);
            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                throw new RuntimeException('No se encontro la hoja principal del archivo.');
            }

            $xml = $this->loadXml($sheetXml);
            $rows = [];

            foreach (($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: []) as $rowNode) {
                $rowNumber = (int) ($rowNode['r'] ?? 0);
                $cells = [];
                $maxIndex = -1;

                foreach (($rowNode->xpath('./*[local-name()="c"]') ?: []) as $cellNode) {
                    $reference = (string) ($cellNode['r'] ?? '');
                    $column = preg_replace('/\d+/', '', $reference);
                    $index = $this->columnToIndex($column);
                    if ($index < 0) {
                        continue;
                    }

                    $maxIndex = max($maxIndex, $index);
                    $cells[$index] = $this->resolveCellValue($cellNode, $sharedStrings);
                }

                if ($maxIndex < 0) {
                    continue;
                }

                $normalizedCells = [];
                for ($index = 0; $index <= $maxIndex; $index++) {
                    $normalizedCells[$index] = $this->sanitizeCell($cells[$index] ?? '');
                }

                $rows[] = [
                    'row_number' => $rowNumber,
                    'cells' => $normalizedCells,
                ];
            }

            if ($rows === []) {
                throw new RuntimeException('El archivo no contiene filas.');
            }

            $headerRow = null;
            while ($rows !== []) {
                $candidate = array_shift($rows);
                if ($candidate && !$this->isEmptyRow($candidate['cells'])) {
                    $headerRow = $candidate;
                    break;
                }
            }

            if (!$headerRow) {
                throw new RuntimeException('El archivo no contiene encabezados validos.');
            }

            $filteredRows = array_values(array_filter($rows, fn (array $row) => !$this->isEmptyRow($row['cells'])));

            return [
                'headers' => $headerRow['cells'],
                'rows' => $filteredRows,
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml === false) {
            return [];
        }

        $xml = $this->loadXml($sharedStringsXml);
        $root = $xml->children(self::SPREADSHEET_NS);
        $strings = [];

        foreach ($root->si as $stringNode) {
            $texts = [];

            foreach ($stringNode->t as $text) {
                $texts[] = (string) $text;
            }

            foreach ($stringNode->r as $run) {
                $texts[] = (string) ($run->t ?? '');
            }

            $strings[] = implode('', $texts);
        }

        return $strings;
    }

    private function resolveFirstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('No se pudo leer la estructura del archivo XLSX.');
        }

        $workbook = $this->loadXml($workbookXml);
        $sheets = $workbook->xpath('//*[local-name()="sheet"]') ?: [];

        if (!$sheets || !isset($sheets[0])) {
            throw new RuntimeException('El archivo XLSX no contiene hojas.');
        }

        $relationshipId = (string) $sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

        $rels = $this->loadXml($relsXml, self::PACKAGE_REL_NS, 'pkg');
        $relationship = null;

        foreach (($rels->xpath('//*[local-name()="Relationship"]') ?: []) as $candidate) {
            if ((string) ($candidate['Id'] ?? '') === $relationshipId) {
                $relationship = $candidate;
                break;
            }
        }

        if (!$relationship) {
            throw new RuntimeException('No se pudo resolver la hoja principal del archivo XLSX.');
        }

        $target = (string) $relationship['Target'];

        return 'xl/' . ltrim(Str::replace('\\', '/', $target), '/');
    }

    private function resolveCellValue(SimpleXMLElement $cellNode, array $sharedStrings): string
    {
        $type = (string) ($cellNode['t'] ?? '');
        if ($type === 'inlineStr') {
            $texts = [];

            foreach (($cellNode->xpath('./*[local-name()="is"]/*[local-name()="t"]') ?: []) as $text) {
                $texts[] = (string) $text;
            }

            foreach (($cellNode->xpath('./*[local-name()="is"]/*[local-name()="r"]') ?: []) as $run) {
                $texts[] = (string) ($run->t ?? '');
            }

            return $this->sanitizeCell(implode('', $texts));
        }

        $valueNode = $cellNode->xpath('./*[local-name()="v"]');
        $value = $valueNode ? (string) $valueNode[0] : '';

        if ($type === 's') {
            return $this->sanitizeCell($sharedStrings[(int) $value] ?? '');
        }

        if ($type === 'b') {
            return $value === '1' ? '1' : '0';
        }

        return $this->sanitizeCell($value);
    }

    private function loadXml(string $xml, string $namespace = self::SPREADSHEET_NS, string $prefix = 'main'): SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);

        if (!$document) {
            throw new RuntimeException('No se pudo interpretar el archivo Excel.');
        }

        $document->registerXPathNamespace($prefix, $namespace);

        return $document;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper(trim($column));
        if ($column === '') {
            return -1;
        }

        $index = 0;
        foreach (str_split($column) as $character) {
            $index = ($index * 26) + (ord($character) - 64);
        }

        return $index - 1;
    }

    /**
     * @param  array<int, string>  $cells
     */
    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($this->sanitizeCell($cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function sanitizeCell(string $value): string
    {
        $value = str_replace("\xEF\xBB\xBF", '', $value);
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);

        return trim($value);
    }
}

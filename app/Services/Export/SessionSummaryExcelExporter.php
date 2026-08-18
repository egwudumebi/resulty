<?php

namespace App\Services\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SessionSummaryExcelExporter
{
    /** @var array<string, float> */
    private array $columnWidths = [
        'A' => 6,
        'B' => 18,
        'C' => 28,
        'D' => 8,
        'E' => 8,
        'F' => 8,
    ];

    /**
     * @param  array<string, ?string>  $metadata
     * @param  array<int, array<string, mixed>>  $students
     */
    public function export(array $metadata, array $students, string $outputPath): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Session Summary');

        $lastCol = 'F';
        $headerRow = 6;
        $firstDataRow = $headerRow + 1;
        $lastDataRow = $firstDataRow + max(count($students), 1) - 1;

        $this->writeHeader($sheet, $metadata, $lastCol);
        $this->writeTableHeader($sheet, $headerRow, $lastCol);
        $this->writeStudents($sheet, $students, $firstDataRow);
        $this->writeFooter($sheet, $lastDataRow + 2, $lastCol);
        $this->applyLayout($sheet, $headerRow, $lastDataRow, $lastCol);

        (new Xlsx($spreadsheet))->save($outputPath);
    }

    private function writeHeader(Worksheet $sheet, array $metadata, string $lastCol): void
    {
        $sheet->setCellValue('A1', $metadata['university'] ?? 'TANSIAN UNIVERSITY, UMUNYA');
        $sheet->setCellValue('A2', $metadata['faculty'] ?? 'FACULTY OF NATURAL AND APPLIED SCIENCES');
        $sheet->setCellValue('A3', $metadata['department'] ?? 'COMPUTER SCIENCE DEPARTMENT');
        $sheet->setCellValue('A4', $metadata['title'] ?? 'SESSION COMPOSITE SUMMARY SHEET');
        $sheet->setCellValue('A5', 'CUMULATIVE RESULTS (1ST & 2ND SEMESTER COMBINED)');
    }

    private function writeTableHeader(Worksheet $sheet, int $headerRow, string $lastCol): void
    {
        $headers = [
            'A' => 'S/N',
            'B' => 'REG. NO',
            'C' => 'NAME OF STUDENT',
            'D' => 'TC',
            'E' => 'TQP',
            'F' => 'GPA',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col.$headerRow, $label);
        }

        unset($lastCol);
    }

    /**
     * @param  array<int, array<string, mixed>>  $students
     */
    private function writeStudents(Worksheet $sheet, array $students, int $startRow): void
    {
        $row = $startRow;

        foreach ($students as $student) {
            $sheet->setCellValue('A'.$row, $student['serial'] ?? '');
            $sheet->setCellValue('B'.$row, $student['reg_no'] ?? '');
            $sheet->setCellValue('C'.$row, $student['name'] ?? '');
            $sheet->setCellValue('D'.$row, $student['tcu'] ?? 0);
            $sheet->setCellValue('E'.$row, $student['tqp'] ?? 0);
            $sheet->setCellValue('F'.$row, $this->formatGpa($student['gpa'] ?? null));
            $row++;
        }
    }

    private function writeFooter(Worksheet $sheet, int $footerRow, string $lastCol): void
    {
        $sheet->setCellValue('A'.$footerRow, 'SIGNATURES');
        $sheet->setCellValue('A'.($footerRow + 2), 'HEAD OF DEPARTMENT');
        $sheet->setCellValue('C'.($footerRow + 2), 'DEAN OF FACULTY');
        $sheet->setCellValue('E'.($footerRow + 2), 'REGISTRAR');

        $sheet->mergeCells('A'.($footerRow + 1).':B'.($footerRow + 1));
        $sheet->mergeCells('C'.($footerRow + 1).':D'.($footerRow + 1));
        $sheet->mergeCells('E'.($footerRow + 1).':F'.($footerRow + 1));

        unset($lastCol);
    }

    private function applyLayout(Worksheet $sheet, int $headerRow, int $lastDataRow, string $lastCol): void
    {
        foreach ($this->columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        foreach (['A1:'.$lastCol.'1', 'A2:'.$lastCol.'2', 'A3:'.$lastCol.'3', 'A4:'.$lastCol.'4', 'A5:'.$lastCol.'5'] as $range) {
            $sheet->mergeCells($range);
        }

        $center = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:'.$lastCol.'5')->applyFromArray(array_merge($center, [
            'font' => ['bold' => true, 'size' => 11],
        ]));

        $sheet->getStyle('A4:'.$lastCol.'4')->getFont()->setSize(12);
        $sheet->getStyle('A5:'.$lastCol.'5')->getFont()->setBold(false)->setSize(10);

        $tableHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
            ],
        ];

        $sheet->getStyle('A'.$headerRow.':'.$lastCol.$headerRow)->applyFromArray($tableHeaderStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        if ($lastDataRow >= $headerRow + 1) {
            $sheet->getStyle('A'.($headerRow + 1).':'.$lastCol.$lastDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->getStyle('A'.($headerRow + 1).':A'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.($headerRow + 1).':F'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('D'.($headerRow + 1).':F'.$lastDataRow)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle('D'.($headerRow + 1).':E'.$lastDataRow)->getNumberFormat()->setFormatCode('0');
        }

        $footerRow = $lastDataRow + 2;
        $sheet->getStyle('A'.$footerRow)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A'.($footerRow + 2).':'.$lastCol.($footerRow + 2))->getFont()->setSize(9);

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    private function formatGpa(mixed $gpa): string|float
    {
        if ($gpa === null || $gpa === '') {
            return '';
        }

        return round((float) $gpa, 2);
    }
}

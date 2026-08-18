<?php

namespace App\Services\Grading;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SemesterResultProcessor
{
    public function __construct(
        private SemesterSheetParser $parser,
    ) {}

    /**
     * @return array{parsed: array, output_path: string}
     */
    public function process(string $inputPath, string $outputPath): array
    {
        $parsed = $this->parser->parse($inputPath);

        $spreadsheet = IOFactory::load($inputPath);
        $sheet = $spreadsheet->getActiveSheet();

        $tcuCol = $parsed['tcu_col'] ?? 'X';
        $tqpCol = $parsed['tqp_col'] ?? 'Y';
        $gpaCol = $parsed['gpa_col'] ?? 'Z';

        foreach ($parsed['students'] as $student) {
            $row = $student['row'];
            $sheet->setCellValue($tcuCol.$row, $student['tcu']);
            $sheet->setCellValue($tqpCol.$row, $student['tqp']);
            $sheet->setCellValue($gpaCol.$row, $student['gpa'] ?? '');
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        return [
            'parsed' => $parsed,
            'output_path' => $outputPath,
        ];
    }
}

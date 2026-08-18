<?php

namespace App\Services\Sample;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SemesterTemplateGenerator
{
    /**
     * @var array<int, array{name: string, code: string, credit: int}>
     */
    private array $courses = [
        ['name' => 'OBJECT ORIENTED PROGRAMMING II', 'code' => 'CSC 221', 'credit' => 3],
        ['name' => 'DATA MANAGEMENT II', 'code' => 'CSC 222', 'credit' => 2],
        ['name' => 'WEB TECHNOLOGIES', 'code' => 'CSC 223', 'credit' => 2],
        ['name' => 'COMPUTER ARCHITECTURE', 'code' => 'CSC 224', 'credit' => 2],
        ['name' => 'COMPUTATIONAL SCIENCE AND NUMERICAL METHODS', 'code' => 'CSC 225', 'credit' => 2],
        ['name' => 'DISCRETE STRUCTURE', 'code' => 'CSC 226', 'credit' => 2],
        ['name' => 'INFORMATION MANAGEMENT', 'code' => 'CSC 227', 'credit' => 2],
        ['name' => 'STATISTICAL COMPUTING', 'code' => 'CSC 228', 'credit' => 2],
        ['name' => 'INTRO. ENTREPRENEURAL SILLS ', 'code' => 'GST 223', 'credit' => 2],
    ];

    /**
     * @var array<int, array{serial: int, name: string, reg_no: string, scores: array<int, array{score: int, grade: string}>}>
     */
    private array $students = [
        [
            'serial' => 1,
            'name' => 'ADAEZE OKONKWO',
            'reg_no' => 'TUN/CS/21/1042',
            'scores' => [
                ['score' => 41, 'grade' => 'E'], ['score' => 51, 'grade' => 'C'], ['score' => 40, 'grade' => 'E'],
                ['score' => 40, 'grade' => 'E'], ['score' => 50, 'grade' => 'C'], ['score' => 43, 'grade' => 'E'],
                ['score' => 40, 'grade' => 'E'], ['score' => 42, 'grade' => 'E'], ['score' => 43, 'grade' => 'E'],
            ],
        ],
        [
            'serial' => 2,
            'name' => 'CHINEDU EZE',
            'reg_no' => 'TUN/CS/21/1043',
            'scores' => [
                ['score' => 50, 'grade' => 'C'], ['score' => 50, 'grade' => 'C'], ['score' => 40, 'grade' => 'E'],
                ['score' => 40, 'grade' => 'E'], ['score' => 53, 'grade' => 'C'], ['score' => 50, 'grade' => 'C'],
                ['score' => 68, 'grade' => 'B'], ['score' => 50, 'grade' => 'C'], ['score' => 60, 'grade' => 'B'],
            ],
        ],
        [
            'serial' => 3,
            'name' => 'NGOZI IBRAHIM',
            'reg_no' => 'TUN/CS/21/1044',
            'scores' => [
                ['score' => 41, 'grade' => 'E'], ['score' => 47, 'grade' => 'D'], ['score' => 60, 'grade' => 'B'],
                ['score' => 40, 'grade' => 'E'], ['score' => 40, 'grade' => 'E'], ['score' => 51, 'grade' => 'C'],
                ['score' => 55, 'grade' => 'C'], ['score' => 70, 'grade' => 'A'], ['score' => 45, 'grade' => 'D'],
            ],
        ],
        [
            'serial' => 4,
            'name' => 'EMEKA NWANKWO',
            'reg_no' => 'TUN/CS/21/1045',
            'scores' => [
                ['score' => 46, 'grade' => 'D'], ['score' => 72, 'grade' => 'A'], ['score' => 56, 'grade' => 'C'],
                ['score' => 43, 'grade' => 'E'], ['score' => 50, 'grade' => 'C'], ['score' => 70, 'grade' => 'A'],
                ['score' => 86, 'grade' => 'A'], ['score' => 74, 'grade' => 'A'], ['score' => 62, 'grade' => 'B'],
            ],
        ],
        [
            'serial' => 5,
            'name' => 'FUNKE ADEBAYO',
            'reg_no' => 'TUN/CS/21/1046',
            'scores' => [
                ['score' => 60, 'grade' => 'B'], ['score' => 55, 'grade' => 'C'], ['score' => 57, 'grade' => 'C'],
                ['score' => 40, 'grade' => 'E'], ['score' => 50, 'grade' => 'C'], ['score' => 52, 'grade' => 'C'],
                ['score' => 65, 'grade' => 'B'], ['score' => 60, 'grade' => 'B'], ['score' => 70, 'grade' => 'A'],
            ],
        ],
    ];

    public function generate(string $outputPath): string
    {
        $directory = dirname($outputPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $this->writeHeaders($sheet);
        $this->writeStudents($sheet);
        $this->writeFooter($sheet, 10 + count($this->students) + 1);
        $this->applyMerges($sheet);
        $this->applyStyles($sheet, 10 + count($this->students));

        (new Xlsx($spreadsheet))->save($outputPath);

        return $outputPath;
    }

    private function writeHeaders(Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'TANSIAN UNIVERSITY, UMUNYA');
        $sheet->setCellValue('A2', 'FACULTY OF NATURAL AND APPLIED SCIENCES');
        $sheet->setCellValue('A3', 'COMPUTER SCIENCE DEPARTMENT');
        $sheet->setCellValue('A4', '200 LEVEL      2ND SEMESTER                                  COMPOSITE RESULT SHEET                                                             2023/2024 SESSION');
        $sheet->setCellValue('D5', 'COURSES AND CREDIT UNITS');
        $sheet->setCellValue('X5', 'CUMULATIVE');

        $startCol = 4;
        foreach ($this->courses as $index => $course) {
            $col = $startCol + ($index * 2);
            $colLetter = $this->columnLetter($col);
            $sheet->setCellValue($colLetter.'6', $course['name']);
            $sheet->setCellValue($colLetter.'7', $course['code']);
            $sheet->setCellValue($colLetter.'8', $course['credit']);
        }

        $totalCredits = array_sum(array_column($this->courses, 'credit'));
        $sheet->setCellValue('X7', 'TCU');
        $sheet->setCellValue('Y7', 'TQP');
        $sheet->setCellValue('Z7', 'GPA');
        $sheet->setCellValue('X8', $totalCredits);

        $sheet->setCellValue('A9', 'S/N');
        $sheet->setCellValue('B9', 'NAME OF STUDENTS');
        $sheet->setCellValue('C9', 'REG. NO');

        foreach ($this->courses as $index => $course) {
            unset($course);
            $col = $startCol + ($index * 2);
            $colLetter = $this->columnLetter($col);
            $gradeCol = $this->columnLetter($col + 1);
            $sheet->setCellValue($colLetter.'9', 'SCORE');
            $sheet->setCellValue($gradeCol.'9', 'GRADE');
        }
    }

    private function writeStudents(Worksheet $sheet): void
    {
        $row = 10;
        $startCol = 4;
        $totalCredits = array_sum(array_column($this->courses, 'credit'));

        foreach ($this->students as $student) {
            $sheet->setCellValue('A'.$row, $student['serial']);
            $sheet->setCellValue('B'.$row, $student['name']);
            $sheet->setCellValue('C'.$row, $student['reg_no']);

            foreach ($student['scores'] as $index => $entry) {
                $col = $startCol + ($index * 2);
                $colLetter = $this->columnLetter($col);
                $gradeCol = $this->columnLetter($col + 1);
                $sheet->setCellValue($colLetter.$row, $entry['score']);
                $sheet->setCellValue($gradeCol.$row, $entry['grade']);
            }

            $sheet->setCellValue('X'.$row, $totalCredits);
            // TQP and GPA left blank for Resulty to calculate

            $row++;
        }
    }

    private function writeFooter(Worksheet $sheet, int $row): void
    {
        $sheet->setCellValue('A'.$row, str_repeat('_', 120));
        $sheet->setCellValue('A'.($row + 1), 'HEAD OF DEPARTMENT                                DEAN OF FACULTY                                         REGISTRAR                                             VICE CHANCELLOR');
    }

    private function applyMerges(Worksheet $sheet): void
    {
        foreach (['A1:AF1', 'A2:AF2', 'A3:AF3', 'A4:AF4'] as $range) {
            $sheet->mergeCells($range);
        }

        $sheet->mergeCells('D5:W5');
        $sheet->mergeCells('X5:Z5');
        $sheet->mergeCells('A5:C8');
        $sheet->mergeCells('A9:C9');

        foreach ($this->courses as $index => $course) {
            unset($course);
            $col = 4 + ($index * 2);
            $start = $this->columnLetter($col);
            $end = $this->columnLetter($col + 1);
            $sheet->mergeCells("{$start}6:{$end}6");
            $sheet->mergeCells("{$start}7:{$end}7");
            $sheet->mergeCells("{$start}8:{$end}8");
        }

        $footerRow = 10 + count($this->students) + 1;
        $sheet->mergeCells('A'.$footerRow.':AF'.$footerRow);
        $sheet->mergeCells('A'.($footerRow + 1).':AF'.($footerRow + 1));
    }

    private function applyStyles(Worksheet $sheet, int $lastStudentRow): void
    {
        foreach ([1, 2, 3, 4] as $row) {
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('D5:Z8')->getFont()->setBold(true);
        $sheet->getStyle('A9:U9')->getFont()->setBold(true);
        $sheet->getStyle('A9:Z'.$lastStudentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('D5:W5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');

        $sheet->getStyle('X5:Z5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA');
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}

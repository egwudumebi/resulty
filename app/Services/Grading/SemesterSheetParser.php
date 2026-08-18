<?php

namespace App\Services\Grading;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SemesterSheetParser
{
    public function __construct(
        private GradePointCalculator $calculator,
    ) {}

    /**
     * @return array{
     *     metadata: array<string, ?string>,
     *     courses: array<int, array{col: string, code: ?string, name: ?string, credit: float}>,
     *     tcu_col: ?string,
     *     tqp_col: ?string,
     *     gpa_col: ?string,
     *     students: array<int, array{
     *         row: int,
     *         serial: ?int,
     *         name: ?string,
     *         reg_no: ?string,
     *         courses: array<int, array{credit: float, grade: ?string}>,
     *         tcu: int,
     *         tqp: int,
     *         gpa: ?float
     *     }>
     * }
     */
    public function parse(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $layout = $this->detectLayout($sheet);
        $metadata = $this->extractMetadata($sheet);
        $students = [];

        foreach ($layout['student_rows'] as $row) {
            if ($this->isFooterRow($sheet, $row)) {
                continue;
            }

            $courseGrades = [];
            foreach ($layout['courses'] as $course) {
                $grade = $this->cellValue($sheet, $course['grade_col'].$row);
                $courseGrades[] = [
                    'credit' => $course['credit'],
                    'grade' => $this->normalizeGrade($grade),
                ];
            }

            $result = $this->calculator->calculate($courseGrades);

            $students[] = [
                'row' => $row,
                'serial' => $this->toInt($this->cellValue($sheet, 'A'.$row)),
                'name' => $this->stringValue($this->cellValue($sheet, 'B'.$row)),
                'reg_no' => $this->stringValue($this->cellValue($sheet, 'C'.$row)),
                'courses' => $courseGrades,
                'tcu' => $result['tcu'],
                'tqp' => $result['tqp'],
                'gpa' => $result['gpa'],
            ];
        }

        return [
            'metadata' => $metadata,
            'courses' => $layout['courses'],
            'tcu_col' => $layout['tcu_col'],
            'tqp_col' => $layout['tqp_col'],
            'gpa_col' => $layout['gpa_col'],
            'students' => $students,
        ];
    }

    /**
     * @return array{
     *     courses: array<int, array{col: string, code: ?string, name: ?string, credit: float, grade_col: string}>,
     *     tcu_col: ?string,
     *     tqp_col: ?string,
     *     gpa_col: ?string,
     *     student_rows: array<int, int>
     * }
     */
    private function detectLayout(Worksheet $sheet): array
    {
        $headerRow = null;
        $creditRow = null;

        for ($row = 1; $row <= 20; $row++) {
            $rowText = strtoupper($this->rowText($sheet, $row));
            if (str_contains($rowText, 'TQP') && str_contains($rowText, 'GPA')) {
                $headerRow = $row;
                $creditRow = $row + 1;
                break;
            }
        }

        if ($headerRow === null || $creditRow === null) {
            throw new \RuntimeException('Could not detect course header row (TCU/TQP/GPA) in the spreadsheet.');
        }

        $subHeaderRow = $creditRow + 1;
        $nameRow = max(1, $headerRow - 1);
        $courses = [];
        $skipLabels = ['TCU', 'TC', 'TQP', 'GPA', 'SCORE', 'GRADE', 'S/N'];

        for ($col = 4; $col <= 40; $col++) {
            $colLetter = $this->columnLetter($col);
            $courseCode = strtoupper(trim((string) ($this->cellValue($sheet, $colLetter.$headerRow) ?? '')));
            $credit = (float) ($this->cellValue($sheet, $colLetter.$creditRow) ?? 0);

            if ($credit <= 0 || in_array($courseCode, $skipLabels, true)) {
                continue;
            }

            $subHeader = strtoupper(trim((string) ($this->cellValue($sheet, $colLetter.$subHeaderRow) ?? '')));
            if ($subHeader !== '' && $subHeader !== 'SCORE') {
                continue;
            }

            $gradeCol = $this->columnLetter($col + 1);
            $gradeHeader = strtoupper(trim((string) ($this->cellValue($sheet, $gradeCol.$subHeaderRow) ?? '')));

            if ($subHeader === 'SCORE' && $gradeHeader !== 'GRADE') {
                continue;
            }

            $courses[] = [
                'col' => $colLetter,
                'code' => $this->stringValue($this->cellValue($sheet, $colLetter.$headerRow)),
                'name' => $this->stringValue($this->cellValue($sheet, $colLetter.$nameRow)),
                'credit' => $credit,
                'grade_col' => $gradeCol,
            ];
        }

        if ($courses === []) {
            throw new \RuntimeException('No course columns detected in the spreadsheet.');
        }

        $tcuCol = $tqpCol = $gpaCol = null;
        for ($col = 4; $col <= 50; $col++) {
            $colLetter = $this->columnLetter($col);
            $label = strtoupper(trim((string) $this->cellValue($sheet, $colLetter.$headerRow)));
            if ($label === 'TCU' || $label === 'TC') {
                $tcuCol = $colLetter;
            } elseif ($label === 'TQP') {
                $tqpCol = $colLetter;
            } elseif ($label === 'GPA') {
                $gpaCol = $colLetter;
            }
        }

        $studentRows = [];
        for ($row = $subHeaderRow + 1; $row <= $sheet->getHighestRow(); $row++) {
            if ($this->isFooterRow($sheet, $row)) {
                continue;
            }

            $serial = $this->cellValue($sheet, 'A'.$row);
            $firstGrade = $this->cellValue($sheet, $courses[0]['grade_col'].$row);

            if ($serial === null && ($firstGrade === null || $firstGrade === '')) {
                continue;
            }

            if (! is_numeric($serial) && ($firstGrade === null || $firstGrade === '')) {
                continue;
            }

            $studentRows[] = $row;
        }

        return [
            'courses' => $courses,
            'tcu_col' => $tcuCol,
            'tqp_col' => $tqpCol,
            'gpa_col' => $gpaCol,
            'student_rows' => $studentRows,
        ];
    }

    /**
     * @return array<string, ?string>
     */
    private function extractMetadata(Worksheet $sheet): array
    {
        return [
            'university' => $this->stringValue($this->cellValue($sheet, 'A1')),
            'faculty' => $this->stringValue($this->cellValue($sheet, 'A2')),
            'department' => $this->stringValue($this->cellValue($sheet, 'A3')),
            'title' => $this->stringValue($this->cellValue($sheet, 'A4')),
        ];
    }

    private function isFooterRow(Worksheet $sheet, int $row): bool
    {
        $text = strtoupper($this->rowText($sheet, $row));

        if (str_contains($text, 'HEAD OF DEPARTMENT')) {
            return true;
        }

        if (str_contains($text, 'TANSIAN UNIVERSITY') && $row > 10) {
            return true;
        }

        $firstCell = (string) ($this->cellValue($sheet, 'A'.$row) ?? '');

        return str_repeat('_', 10) === substr(str_replace(' ', '', $firstCell), 0, 10)
            && strlen($firstCell) > 20;
    }

    private function rowText(Worksheet $sheet, int $row): string
    {
        $parts = [];
        for ($col = 1; $col <= 30; $col++) {
            $value = $this->cellValue($sheet, $this->columnLetter($col).$row);
            if ($value !== null && $value !== '') {
                $parts[] = (string) $value;
            }
        }

        return implode(' ', $parts);
    }

    private function cellValue(Worksheet $sheet, string $coordinate): mixed
    {
        $value = $sheet->getCell($coordinate)->getCalculatedValue();

        return is_string($value) ? trim($value) : $value;
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

    private function normalizeGrade(mixed $grade): ?string
    {
        if ($grade === null || $grade === '') {
            return null;
        }

        return strtoupper(trim((string) $grade));
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}

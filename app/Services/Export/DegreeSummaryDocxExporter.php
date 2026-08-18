<?php

namespace App\Services\Export;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Cell as CellStyle;
use App\Services\Grading\ClassOfDegreeFormatter;

class DegreeSummaryDocxExporter
{
    public function __construct(
        private ClassOfDegreeFormatter $classFormatter,
    ) {}
    /** @var array<int, string> */
    private array $yearLabels = [
        1 => 'YEAR ONE',
        2 => 'YEAR TWO',
        3 => 'YEAR THREE',
        4 => 'YEAR FOUR',
    ];

    /**
     * @param  array<string, ?string>  $metadata
     * @param  array<int, array<string, mixed>>  $students
     */
    public function export(array $metadata, array $students, string $outputPath): void
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 720,
            'marginRight' => 720,
            'marginTop' => 720,
            'marginBottom' => 720,
        ]);

        $center = ['alignment' => Jc::CENTER];
        $headerFont = ['bold' => true, 'size' => 11];
        $titleFont = ['bold' => true, 'size' => 10];

        $section->addText($metadata['university'] ?? 'TANSIAN UNIVERSITY UMUNYA', $headerFont, $center);
        $section->addText($metadata['faculty'] ?? 'FACULTY OF NATURAL AND APPLIED SCIENCES', $headerFont, $center);
        $section->addText($metadata['department'] ?? 'DEPARTMENT OF COMPUTER SCIENCE', $headerFont, $center);
        $section->addText($metadata['title'] ?? 'DEGREE EXAMINATION RESULT (SUMMARY)', $titleFont, $center);
        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '333333',
            'cellMargin' => 40,
            'alignment' => Jc::CENTER,
        ]);

        $this->addHeaderRows($table);
        $this->addStudentRows($table, $students);

        $section->addTextBreak(2);
        $this->addSignatureBlock($section);

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);
    }

    private function addHeaderRows(\PhpOffice\PhpWord\Element\Table $table): void
    {
        $headerBg = ['bgColor' => '1E3A5F', 'valign' => 'center'];
        $headerText = ['bold' => true, 'color' => 'FFFFFF', 'size' => 8];
        $center = ['alignment' => Jc::CENTER];

        // Row 1 — biodata + year groups + cumulative
        $table->addRow(400, ['tblHeader' => true]);

        foreach (['S/N', 'MATRIC NO', 'NAME', 'STATE OF ORIGIN', 'DATE OF BIRTH', 'SEX'] as $label) {
            $this->headerCell($table, 700, $label, $headerBg, $headerText, $center, CellStyle::VMERGE_RESTART);
        }

        foreach ($this->yearLabels as $label) {
            $this->headerCell($table, 1800, $label, $headerBg, $headerText, $center, null, 3);
        }

        foreach (['CTC', 'CTQP', 'FCGPA', 'CLASS OF DEGREE'] as $label) {
            $width = $label === 'CLASS OF DEGREE' ? 900 : 650;
            $this->headerCell($table, $width, $label, $headerBg, $headerText, $center, CellStyle::VMERGE_RESTART);
        }

        // Row 2 — TC / TQP / GPA under each year
        $table->addRow(350, ['tblHeader' => true]);

        for ($i = 0; $i < 6; $i++) {
            $this->headerCell($table, null, '', $headerBg, $headerText, $center, CellStyle::VMERGE_CONTINUE);
        }

        for ($year = 1; $year <= 4; $year++) {
            foreach (['TC', 'TQP', 'GPA'] as $metric) {
                $this->headerCell($table, 600, $metric, $headerBg, $headerText, $center);
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $this->headerCell($table, null, '', $headerBg, $headerText, $center, CellStyle::VMERGE_CONTINUE);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $students
     */
    private function addStudentRows(\PhpOffice\PhpWord\Element\Table $table, array $students): void
    {
        $center = ['alignment' => Jc::CENTER];
        $left = ['alignment' => Jc::START];
        $dataFont = ['size' => 8];

        foreach ($students as $student) {
            $table->addRow();
            $years = $student['years'] ?? [];

            $this->dataCell($table, 700, (string) ($student['serial'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 1100, (string) ($student['reg_no'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 1600, (string) ($student['name'] ?? ''), $dataFont, $left);
            $this->dataCell($table, 1100, (string) ($student['state'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 1100, (string) ($student['dob'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 600, (string) ($student['sex'] ?? ''), $dataFont, $center);

            for ($year = 1; $year <= 4; $year++) {
                $yearData = $years[$year] ?? null;
                $this->dataCell($table, 600, $yearData ? (string) $yearData['tc'] : '', $dataFont, $center);
                $this->dataCell($table, 600, $yearData ? (string) $yearData['tqp'] : '', $dataFont, $center);
                $this->dataCell($table, 600, $yearData && $yearData['gpa'] !== null ? number_format((float) $yearData['gpa'], 2) : '', $dataFont, $center);
            }

            $this->dataCell($table, 650, (string) ($student['ctc'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 650, (string) ($student['ctqp'] ?? ''), $dataFont, $center);
            $this->dataCell($table, 650, $student['fcgpa'] !== null ? number_format((float) $student['fcgpa'], 2) : '', $dataFont, $center);
            $this->classOfDegreeCell($table, 900, $student['class_of_degree'] ?? null, $dataFont, $center);
        }
    }

    /**
     * @param  array<string, mixed>  $fontStyle
     * @param  array<string, mixed>  $paragraphStyle
     */
    private function classOfDegreeCell(
        \PhpOffice\PhpWord\Element\Table $table,
        int $width,
        ?string $code,
        array $fontStyle,
        array $paragraphStyle,
    ): void {
        $cell = $table->addCell($width, ['valign' => 'center']);
        $parts = $this->classFormatter->parts($code);

        if ($parts === []) {
            return;
        }

        $textRun = $cell->addTextRun($paragraphStyle);

        foreach ($parts as $part) {
            $textRun->addText($part['text'], array_merge($fontStyle, array_filter([
                'bold' => $part['bold'] ?? false,
                'superScript' => $part['superScript'] ?? false,
            ])));
        }
    }

    private function addSignatureBlock(\PhpOffice\PhpWord\Element\Section $section): void
    {
        $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $sigTable->addRow();

        foreach (['HEAD OF DEPARTMENT', 'DEAN OF FACULTY', 'REGISTRAR', 'VICE CHANCELLOR'] as $title) {
            $cell = $sigTable->addCell(2800);
            $cell->addText(str_repeat(' ', 4).str_repeat('.', 28), ['size' => 9], ['alignment' => Jc::CENTER]);
            $cell->addText($title, ['size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
    }

    /**
     * @param  array<string, mixed>  $cellStyle
     * @param  array<string, mixed>  $fontStyle
     * @param  array<string, mixed>  $paragraphStyle
     */
    private function headerCell(
        \PhpOffice\PhpWord\Element\Table $table,
        ?int $width,
        string $text,
        array $cellStyle,
        array $fontStyle,
        array $paragraphStyle,
        ?string $vMerge = null,
        ?int $gridSpan = null,
    ): void {
        $style = $cellStyle;

        if ($vMerge !== null) {
            $style['vMerge'] = $vMerge;
        }

        if ($gridSpan !== null) {
            $style['gridSpan'] = $gridSpan;
        }

        $cell = $table->addCell($width ?? 600, $style);
        if ($text !== '') {
            $cell->addText($text, $fontStyle, $paragraphStyle);
        }
    }

    /**
     * @param  array<string, mixed>  $fontStyle
     * @param  array<string, mixed>  $paragraphStyle
     */
    private function dataCell(
        \PhpOffice\PhpWord\Element\Table $table,
        int $width,
        string $text,
        array $fontStyle,
        array $paragraphStyle,
    ): void {
        $table->addCell($width, ['valign' => 'center'])
            ->addText($text, $fontStyle, $paragraphStyle);
    }
}

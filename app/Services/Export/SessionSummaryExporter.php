<?php

namespace App\Services\Export;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Table;

class SessionSummaryExporter
{
    /**
     * @param  array<string, ?string>  $metadata
     * @param  array<int, array<string, mixed>>  $students
     */
    public function export(array $metadata, array $students, string $outputPath, int $maxYears = 4): void
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop' => 800,
            'marginBottom' => 800,
            'marginLeft' => 800,
            'marginRight' => 800,
        ]);

        foreach ([
            $metadata['university'] ?? 'TANSIAN UNIVERSITY UMUNYA',
            $metadata['faculty'] ?? 'FACULTY OF NATURAL AND APPLIED SCIENCES',
            $metadata['department'] ?? 'DEPARTMENT OF COMPUTER SCIENCE',
            $metadata['title'] ?? 'DEGREE EXAMINATION RESULT (SUMMARY)',
        ] as $line) {
            $section->addText(strtoupper((string) $line), ['bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);
        }

        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
        ]);

        $this->addHeaderRow($table, $maxYears);
        $this->addSubHeaderRow($table, $maxYears);

        foreach ($students as $student) {
            $this->addStudentRow($table, $student, $maxYears);
        }

        $section->addTextBreak(1);
        $section->addText(
            '……………………………………                           ………………………………………                    ………………………………..                         ……………………………………………',
            null,
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'HEAD OF DEPARTMENT                                DEAN OF FACULTY                                     REGISTRAR                                           VICE CHANCELLOR',
            ['bold' => true],
            ['alignment' => Jc::CENTER]
        );

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);
    }

    private function addHeaderRow(\PhpOffice\PhpWord\Element\Table $table, int $maxYears): void
    {
        $table->addRow();
        foreach (['S/N', 'MATRIC NO', 'NAME', 'STATE OF ORIGIN', 'DATE OF BIRTH', 'SEX'] as $heading) {
            $table->addCell(900, $this->headerCell())->addText($heading, $this->bold(), $this->center());
        }

        for ($year = 1; $year <= $maxYears; $year++) {
            $label = match ($year) {
                1 => 'YEAR   ONE',
                2 => 'YEAR  TWO',
                3 => 'YEAR   THREE',
                default => 'YEAR   FOUR',
            };
            $table->addCell(1800, array_merge($this->headerCell(), ['gridSpan' => 3]))->addText($label, $this->bold(), $this->center());
        }

        foreach (['CTC', 'CTQP', 'FCGPA', 'CLASS OF DEGREE'] as $heading) {
            $table->addCell(900, $this->headerCell())->addText($heading, $this->bold(), $this->center());
        }
    }

    private function addSubHeaderRow(\PhpOffice\PhpWord\Element\Table $table, int $maxYears): void
    {
        $table->addRow();
        for ($i = 0; $i < 6; $i++) {
            $table->addCell(900, $this->headerCell())->addText('', $this->bold(), $this->center());
        }

        for ($year = 1; $year <= $maxYears; $year++) {
            foreach (['TC', 'TQP', 'GPA'] as $heading) {
                $table->addCell(600, $this->headerCell())->addText($heading, $this->bold(), $this->center());
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $table->addCell(900, $this->headerCell())->addText('', $this->bold(), $this->center());
        }
    }

    /**
     * @param  array<string, mixed>  $student
     */
    private function addStudentRow(\PhpOffice\PhpWord\Element\Table $table, array $student, int $maxYears): void
    {
        $table->addRow();
        $table->addCell(900)->addText((string) ($student['serial'] ?? ''), null, $this->center());
        $table->addCell(900)->addText((string) ($student['reg_no'] ?? ''), null, $this->center());
        $table->addCell(1200)->addText((string) ($student['name'] ?? ''), null, $this->center());
        $table->addCell(900)->addText((string) ($student['state'] ?? ''), null, $this->center());
        $table->addCell(900)->addText((string) ($student['dob'] ?? ''), null, $this->center());
        $table->addCell(600)->addText((string) ($student['sex'] ?? ''), null, $this->center());

        for ($year = 1; $year <= $maxYears; $year++) {
            $yearData = $student['years'][$year] ?? null;
            $table->addCell(600)->addText($yearData ? (string) $yearData['tc'] : '', null, $this->center());
            $table->addCell(600)->addText($yearData ? (string) $yearData['tqp'] : '', null, $this->center());
            $table->addCell(600)->addText($yearData && $yearData['gpa'] !== null ? number_format((float) $yearData['gpa'], 2) : '', null, $this->center());
        }

        $table->addCell(900)->addText((string) ($student['ctc'] ?? ''), null, $this->center());
        $table->addCell(900)->addText((string) ($student['ctqp'] ?? ''), null, $this->center());
        $table->addCell(900)->addText(isset($student['fcgpa']) ? number_format((float) $student['fcgpa'], 2) : '', null, $this->center());
        $table->addCell(900)->addText((string) ($student['class_of_degree'] ?? ''), null, $this->center());
    }

    /**
     * @return array<string, mixed>
     */
    private function headerCell(): array
    {
        return ['valign' => 'center'];
    }

    /**
     * @return array<string, bool>
     */
    private function bold(): array
    {
        return ['bold' => true];
    }

    /**
     * @return array<string, string>
     */
    private function center(): array
    {
        return ['alignment' => Jc::CENTER];
    }
}

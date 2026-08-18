<?php

namespace Tests\Feature;

use App\Services\Grading\SemesterResultProcessor;
use App\Services\Grading\SemesterSheetParser;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SemesterResultTest extends TestCase
{
    private string $samplePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->samplePath = '/home/tfnbackend/Downloads/dumebi sample 200 L 2ND SEMESTER COMPOSITE RESULT.xlsx';

        if (! file_exists($this->samplePath)) {
            $this->markTestSkipped('Sample semester file not available.');
        }
    }

    public function test_parses_sample_semester_sheet(): void
    {
        $parser = app(SemesterSheetParser::class);
        $parsed = $parser->parse($this->samplePath);

        $this->assertGreaterThan(0, count($parsed['courses']));
        $this->assertGreaterThan(40, count($parsed['students']));
        $this->assertSame(19, $parsed['students'][1]['tcu']);
        $this->assertSame(53, $parsed['students'][1]['tqp']);
        $this->assertSame(2.79, $parsed['students'][1]['gpa']);
    }

    public function test_processes_sample_and_writes_tqp_gpa(): void
    {
        $output = storage_path('app/processed/test-output.xlsx');
        @mkdir(dirname($output), 0777, true);

        $processor = app(SemesterResultProcessor::class);
        $processor->process($this->samplePath, $output);

        $this->assertFileExists($output);

        $sheet = IOFactory::load($output)->getActiveSheet();
        $this->assertSame(53, (int) $sheet->getCell('Y11')->getCalculatedValue());
        $this->assertEqualsWithDelta(2.79, (float) $sheet->getCell('Z11')->getCalculatedValue(), 0.01);

        @unlink($output);
    }

    public function test_dashboard_loads(): void
    {
        $this->get('/results')->assertOk()->assertSee('Results workspace');
    }

    public function test_landing_page_has_cta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Composite results')
            ->assertSee('accurately')
            ->assertSee('Go to results workspace');
    }

    public function test_process_semester_downloads_file(): void
    {
        $this->withoutMiddleware();

        $sample = storage_path('app/samples/semester-composite-sample.xlsx');
        if (! file_exists($sample)) {
            app(\App\Services\Sample\SemesterTemplateGenerator::class)->generate($sample);
        }

        $response = $this->post('/semester/process', [
            'semester_file' => new \Illuminate\Http\UploadedFile(
                $sample,
                'semester-composite-sample.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true,
            ),
        ]);

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\Export\DegreeSummaryDocxExporter;
use App\Services\Export\SessionSummaryExcelExporter;
use App\Services\Grading\BiodataParser;
use App\Services\Grading\SemesterResultProcessor;
use App\Services\Grading\SemesterSheetParser;
use App\Services\Grading\SessionSummaryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResultController extends Controller
{
    public function __construct(
        private SemesterResultProcessor $semesterProcessor,
        private SemesterSheetParser $semesterParser,
        private SessionSummaryBuilder $sessionBuilder,
        private SessionSummaryExcelExporter $sessionExcelExporter,
        private DegreeSummaryDocxExporter $degreeDocxExporter,
        private BiodataParser $biodataParser,
    ) {}

    public function home()
    {
        return view('landing');
    }

    public function results()
    {
        return view('dashboard');
    }

    public function processSemester(Request $request): BinaryFileResponse
    {
        $request->validate([
            'semester_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $token = Str::uuid()->toString();
        $inputPath = $request->file('semester_file')->storeAs('uploads', $token.'-input.xlsx');
        $outputPath = 'processed/'.$token.'-semester.xlsx';

        Storage::makeDirectory('processed');

        $this->semesterProcessor->process(
            Storage::path($inputPath),
            Storage::path($outputPath),
        );

        $filename = 'semester-result-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->download(Storage::path($outputPath), $filename)->deleteFileAfterSend(true);
    }

    public function previewSemester(Request $request)
    {
        $request->validate([
            'semester_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $path = $request->file('semester_file')->getRealPath();
        $parsed = $this->semesterParser->parse($path);

        return response()->json([
            'metadata' => $parsed['metadata'],
            'course_count' => count($parsed['courses']),
            'courses' => $parsed['courses'],
            'student_count' => count($parsed['students']),
            'students' => array_map(fn ($s) => [
                'serial' => $s['serial'],
                'name' => $s['name'],
                'reg_no' => $s['reg_no'],
                'tcu' => $s['tcu'],
                'tqp' => $s['tqp'],
                'gpa' => $s['gpa'],
            ], array_slice($parsed['students'], 0, 10)),
        ]);
    }

    public function processSession(Request $request): BinaryFileResponse
    {
        $request->validate([
            'first_semester' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'second_semester' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'biodata_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $first = $this->semesterParser->parse($request->file('first_semester')->getRealPath());
        $second = $this->semesterParser->parse($request->file('second_semester')->getRealPath());

        $biodata = [];
        if ($request->hasFile('biodata_file')) {
            $biodata = $this->biodataParser->parseCsv($request->file('biodata_file')->getRealPath());
        }

        $students = $this->sessionBuilder->combineSemesters([$first, $second], $biodata);

        $token = Str::uuid()->toString();
        $outputPath = 'processed/'.$token.'-session.xlsx';
        Storage::makeDirectory('processed');

        $metadata = [
            'university' => $first['metadata']['university'] ?? 'TANSIAN UNIVERSITY, UMUNYA',
            'faculty' => $first['metadata']['faculty'] ?? 'FACULTY OF NATURAL AND APPLIED SCIENCES',
            'department' => $first['metadata']['department'] ?? 'COMPUTER SCIENCE DEPARTMENT',
            'title' => $this->sessionTitle($first['metadata']['title'] ?? null),
        ];

        $this->sessionExcelExporter->export($metadata, $students, Storage::path($outputPath));

        return response()->download(Storage::path($outputPath), 'session-summary-'.now()->format('Y-m-d-His').'.xlsx')
            ->deleteFileAfterSend(true);
    }

    public function processDegree(Request $request): BinaryFileResponse
    {
        $request->validate([
            'year' => ['required', 'array', 'min:1'],
            'year.*' => ['integer', 'min:1', 'max:4'],
            'first_semester' => ['required', 'array'],
            'first_semester.*' => ['file', 'mimes:xlsx,xls', 'max:10240'],
            'second_semester' => ['required', 'array'],
            'second_semester.*' => ['file', 'mimes:xlsx,xls', 'max:10240'],
            'biodata_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $years = $request->input('year');
        $biodata = [];
        if ($request->hasFile('biodata_file')) {
            $biodata = $this->biodataParser->parseCsv($request->file('biodata_file')->getRealPath());
        }

        $yearSessions = [];

        foreach ($years as $index => $year) {
            $firstFile = $request->file('first_semester')[$index] ?? null;
            $secondFile = $request->file('second_semester')[$index] ?? null;

            if (! $firstFile || ! $secondFile) {
                continue;
            }

            $first = $this->semesterParser->parse($firstFile->getRealPath());
            $second = $this->semesterParser->parse($secondFile->getRealPath());
            $sessionStudents = $this->sessionBuilder->combineSemesters([$first, $second], $biodata);

            $yearSessions[] = [
                'year' => (int) $year,
                'students' => array_map(fn ($s) => [
                    'serial' => $s['serial'],
                    'reg_no' => $s['reg_no'],
                    'name' => $s['name'],
                    'tcu' => $s['tcu'],
                    'tqp' => $s['tqp'],
                    'gpa' => $s['gpa'],
                    'state' => $s['state'] ?? null,
                    'dob' => $s['dob'] ?? null,
                    'sex' => $s['sex'] ?? null,
                ], $sessionStudents),
            ];
        }

        $students = $this->sessionBuilder->buildDegreeSummary($yearSessions, $biodata);

        $token = Str::uuid()->toString();
        $outputPath = 'processed/'.$token.'-degree.docx';
        Storage::makeDirectory('processed');

        $metadata = [
            'university' => 'TANSIAN UNIVERSITY UMUNYA',
            'faculty' => 'FACULTY OF NATURAL AND APPLIED SCIENCES',
            'department' => 'DEPARTMENT OF COMPUTER SCIENCE',
            'title' => 'DEGREE EXAMINATION RESULT (SUMMARY)',
        ];

        $this->degreeDocxExporter->export($metadata, $students, Storage::path($outputPath));

        return response()->download(Storage::path($outputPath), 'degree-summary-'.now()->format('Y-m-d-His').'.docx')
            ->deleteFileAfterSend(true);
    }

    private function sessionTitle(?string $semesterTitle): string
    {
        if ($semesterTitle) {
            $clean = preg_replace('/\s+COMPOSITE RESULT SHEET.*$/i', '', $semesterTitle) ?? $semesterTitle;
            $clean = trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);

            return $clean.' — SESSION SUMMARY';
        }

        return 'SESSION COMPOSITE SUMMARY SHEET';
    }
}

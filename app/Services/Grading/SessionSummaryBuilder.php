<?php

namespace App\Services\Grading;

class SessionSummaryBuilder
{
    public function __construct(
        private GradePointCalculator $calculator,
    ) {}

    /**
     * @param  array<int, array{students: array<int, array<string, mixed>>}>  $semesters
     * @param  array<int, array<string, mixed>>  $biodata
     * @return array<int, array<string, mixed>>
     */
    public function combineSemesters(array $semesters, array $biodata = []): array
    {
        $combined = [];

        foreach ($semesters as $semesterIndex => $semester) {
            foreach ($semester['students'] as $student) {
                $key = $this->studentKey($student);

                if (! isset($combined[$key])) {
                    $combined[$key] = [
                        'serial' => $student['serial'],
                        'reg_no' => $student['reg_no'],
                        'name' => $student['name'],
                        'tcu' => 0,
                        'tqp' => 0,
                        'gpa' => null,
                    ];
                }

                $combined[$key]['tcu'] += $student['tcu'];
                $combined[$key]['tqp'] += $student['tqp'];

                if ($student['name'] && ! $combined[$key]['name']) {
                    $combined[$key]['name'] = $student['name'];
                }

                if ($student['reg_no'] && ! $combined[$key]['reg_no']) {
                    $combined[$key]['reg_no'] = $student['reg_no'];
                }
            }
        }

        foreach ($combined as $key => $student) {
            $combined[$key]['gpa'] = $student['tcu'] > 0
                ? round($student['tqp'] / $student['tcu'], (int) config('resulty.gpa_decimal_places', 2))
                : null;

            $biodataKey = $student['reg_no'] ?: (string) $student['serial'];
            if (isset($biodata[$biodataKey])) {
                $combined[$key] = array_merge($combined[$key], $biodata[$biodataKey]);
            }
        }

        usort($combined, fn ($a, $b) => ($a['serial'] ?? 0) <=> ($b['serial'] ?? 0));

        return array_values($combined);
    }

    /**
     * @param  array<int, array{year: int, students: array<int, array<string, mixed>>}>  $yearSessions
     * @return array<int, array<string, mixed>>
     */
    public function buildDegreeSummary(array $yearSessions, array $biodata = []): array
    {
        $byStudent = [];

        foreach ($yearSessions as $session) {
            $year = $session['year'];

            foreach ($session['students'] as $student) {
                $key = $this->studentKey($student);

                if (! isset($byStudent[$key])) {
                    $byStudent[$key] = [
                        'serial' => $student['serial'],
                        'reg_no' => $student['reg_no'],
                        'name' => $student['name'],
                        'years' => [],
                        'ctc' => 0,
                        'ctqp' => 0,
                        'fcgpa' => null,
                        'class_of_degree' => null,
                    ];
                }

                $byStudent[$key]['years'][$year] = [
                    'tc' => $student['tcu'],
                    'tqp' => $student['tqp'],
                    'gpa' => $student['gpa'],
                ];

                $byStudent[$key]['ctc'] += $student['tcu'];
                $byStudent[$key]['ctqp'] += $student['tqp'];

                if ($student['name'] && ! $byStudent[$key]['name']) {
                    $byStudent[$key]['name'] = $student['name'];
                }

                if ($student['reg_no'] && ! $byStudent[$key]['reg_no']) {
                    $byStudent[$key]['reg_no'] = $student['reg_no'];
                }
            }
        }

        foreach ($byStudent as $key => $student) {
            $byStudent[$key]['fcgpa'] = $student['ctc'] > 0
                ? round($student['ctqp'] / $student['ctc'], (int) config('resulty.gpa_decimal_places', 2))
                : null;

            $byStudent[$key]['class_of_degree'] = $this->calculator->classOfDegree($byStudent[$key]['fcgpa']);

            $biodataKey = $student['reg_no'] ?: (string) $student['serial'];
            if (isset($biodata[$biodataKey])) {
                $byStudent[$key] = array_merge($byStudent[$key], $biodata[$biodataKey]);
            }
        }

        usort($byStudent, fn ($a, $b) => ($a['serial'] ?? 0) <=> ($b['serial'] ?? 0));

        return array_values($byStudent);
    }

    /**
     * @param  array<string, mixed>  $student
     */
    private function studentKey(array $student): string
    {
        if (! empty($student['reg_no'])) {
            return 'reg:'.strtoupper(trim((string) $student['reg_no']));
        }

        return 'sn:'.(string) ($student['serial'] ?? spl_object_id((object) $student));
    }
}

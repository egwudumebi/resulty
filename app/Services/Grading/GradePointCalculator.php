<?php

namespace App\Services\Grading;

class GradePointCalculator
{
    /**
     * @param  array<string, int>  $gradePoints
     */
    public function __construct(
        private array $gradePoints = [],
        private int $decimalPlaces = 2,
    ) {
        $this->gradePoints = $gradePoints ?: config('resulty.grade_points', []);
        $this->decimalPlaces = $decimalPlaces ?: (int) config('resulty.gpa_decimal_places', 2);
    }

    public function pointsForGrade(?string $grade): int
    {
        if ($grade === null || $grade === '') {
            return 0;
        }

        $normalized = strtoupper(trim($grade));

        return $this->gradePoints[$normalized] ?? 0;
    }

    /**
     * @param  array<int, array{credit: int|float, grade: ?string}>  $courses
     */
    public function calculate(array $courses): array
    {
        $tcu = 0;
        $tqp = 0;

        foreach ($courses as $course) {
            $credit = (float) ($course['credit'] ?? 0);
            $points = $this->pointsForGrade($course['grade'] ?? null);

            $tcu += $credit;
            $tqp += $credit * $points;
        }

        $gpa = $tcu > 0 ? round($tqp / $tcu, $this->decimalPlaces) : null;

        return [
            'tcu' => (int) $tcu,
            'tqp' => (int) round($tqp),
            'gpa' => $gpa,
        ];
    }

    public function classOfDegree(?float $fcgpa): ?string
    {
        if ($fcgpa === null) {
            return null;
        }

        foreach (config('resulty.class_of_degree', []) as $band) {
            if ($fcgpa >= $band['min']) {
                return $band['code'];
            }
        }

        return null;
    }
}

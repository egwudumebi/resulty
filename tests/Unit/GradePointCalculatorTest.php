<?php

namespace Tests\Unit;

use App\Services\Grading\GradePointCalculator;
use PHPUnit\Framework\TestCase;

class GradePointCalculatorTest extends TestCase
{
    public function test_calculates_tqp_and_gpa_for_semester_courses(): void
    {
        $calculator = new GradePointCalculator([
            'A' => 5,
            'B' => 4,
            'C' => 3,
            'D' => 2,
            'E' => 1,
        ]);

        $result = $calculator->calculate([
            ['credit' => 3, 'grade' => 'C'],
            ['credit' => 2, 'grade' => 'C'],
            ['credit' => 2, 'grade' => 'E'],
            ['credit' => 2, 'grade' => 'E'],
            ['credit' => 2, 'grade' => 'C'],
            ['credit' => 2, 'grade' => 'C'],
            ['credit' => 2, 'grade' => 'B'],
            ['credit' => 2, 'grade' => 'C'],
            ['credit' => 2, 'grade' => 'B'],
        ]);

        $this->assertSame(19, $result['tcu']);
        $this->assertSame(53, $result['tqp']);
        $this->assertSame(2.79, $result['gpa']);
    }
}

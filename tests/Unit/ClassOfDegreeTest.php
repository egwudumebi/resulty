<?php

namespace Tests\Unit;

use App\Services\Grading\GradePointCalculator;
use Tests\TestCase;

class ClassOfDegreeTest extends TestCase
{
    public function test_class_of_degree_thresholds(): void
    {
        $calculator = new GradePointCalculator;

        $this->assertSame('21', $calculator->classOfDegree(3.53));
        $this->assertSame('22', $calculator->classOfDegree(3.46));
    }
}

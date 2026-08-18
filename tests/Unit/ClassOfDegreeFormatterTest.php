<?php

namespace Tests\Unit;

use App\Services\Grading\ClassOfDegreeFormatter;
use PHPUnit\Framework\TestCase;

class ClassOfDegreeFormatterTest extends TestCase
{
    public function test_formats_degree_classes_with_superscripts(): void
    {
        $formatter = new ClassOfDegreeFormatter;

        $this->assertSame('1', $formatter->plainText('11'));
        $this->assertSame('2¹', $formatter->plainText('21'));
        $this->assertSame('2²', $formatter->plainText('22'));
        $this->assertSame('3', $formatter->plainText('23'));
        $this->assertSame('4', $formatter->plainText('24'));
    }

    public function test_builds_word_parts_for_superscript_rendering(): void
    {
        $formatter = new ClassOfDegreeFormatter;

        $this->assertSame([
            ['text' => '2', 'bold' => true],
            ['text' => '1', 'superScript' => true],
        ], $formatter->parts('21'));
    }
}

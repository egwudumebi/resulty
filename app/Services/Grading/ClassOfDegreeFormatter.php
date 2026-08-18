<?php

namespace App\Services\Grading;

class ClassOfDegreeFormatter
{
    /**
     * @return array<int, array{text: string, superScript?: bool, bold?: bool}>
     */
    public function parts(?string $code): array
    {
        if ($code === null || $code === '') {
            return [];
        }

        $code = trim($code);

        return match ($code) {
            '1', '11' => [['text' => '1', 'bold' => true]],
            '21' => [
                ['text' => '2', 'bold' => true],
                ['text' => '1', 'superScript' => true],
            ],
            '22' => [
                ['text' => '2', 'bold' => true],
                ['text' => '2', 'superScript' => true],
            ],
            '23', '3' => [['text' => '3', 'bold' => true]],
            '24', '4' => [['text' => '4', 'bold' => true]],
            default => $this->parseCode($code),
        };
    }

    public function plainText(?string $code): string
    {
        $parts = $this->parts($code);

        return implode('', array_map(function (array $part) {
            $text = $part['text'];

            return ($part['superScript'] ?? false)
                ? $this->superscriptChar($text)
                : $text;
        }, $parts));
    }

    /**
     * @return array<int, array{text: string, superScript?: bool, bold?: bool}>
     */
    private function parseCode(string $code): array
    {
        if (strlen($code) === 2 && ctype_digit($code)) {
            $main = $code[0];
            $sub = $code[1];

            if ($main === '1') {
                return [['text' => '1', 'bold' => true]];
            }

            if ($main === '2' && in_array($sub, ['1', '2'], true)) {
                return [
                    ['text' => '2', 'bold' => true],
                    ['text' => $sub, 'superScript' => true],
                ];
            }
        }

        return [['text' => $code, 'bold' => true]];
    }

    private function superscriptChar(string $digit): string
    {
        return match ($digit) {
            '0' => '⁰',
            '1' => '¹',
            '2' => '²',
            '3' => '³',
            '4' => '⁴',
            '5' => '⁵',
            '6' => '⁶',
            '7' => '⁷',
            '8' => '⁸',
            '9' => '⁹',
            default => $digit,
        };
    }
}

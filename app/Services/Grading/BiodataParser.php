<?php

namespace App\Services\Grading;

class BiodataParser
{
    /**
     * @return array<string, array<string, ?string>>
     */
    public function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read biodata file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return [];
        }

        $map = $this->normalizeHeaders($header);
        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $record = [
                'reg_no' => $this->value($row, $map, ['reg_no', 'matric', 'matric_no']),
                'name' => $this->value($row, $map, ['name', 'student_name']),
                'state' => $this->value($row, $map, ['state', 'state_of_origin']),
                'dob' => $this->value($row, $map, ['dob', 'date_of_birth', 'birth_date']),
                'sex' => $this->value($row, $map, ['sex', 'gender']),
            ];

            $key = $record['reg_no'] ?: ($this->value($row, $map, ['serial', 'sn']) ?? null);
            if ($key) {
                $records[strtoupper(trim((string) $key))] = $record;
            }
        }

        fclose($handle);

        return $records;
    }

    /**
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    private function normalizeHeaders(array $header): array
    {
        $map = [];
        foreach ($header as $index => $label) {
            $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $label) ?? '', '_'));
            $map[$key] = $index;
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $row
     * @param  array<string, int>  $map
     * @param  array<int, string>  $keys
     */
    private function value(array $row, array $map, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($map[$key]) && isset($row[$map[$key]]) && trim($row[$map[$key]]) !== '') {
                return trim($row[$map[$key]]);
            }
        }

        return null;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}

<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Modules;

use App\Containers\AppSection\CustomField\Contracts\CustomFieldModuleInterface;
use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ListCustomFieldModule implements CustomFieldModuleInterface
{
    public function type(): CustomFieldTypeEnum
    {
        return CustomFieldTypeEnum::LIST;
    }

    public function validateAndNormalize(array $payload): array
    {
        $validator = Validator::make($payload, [
            'title' => ['nullable', 'string', 'max:255'],
            'columns' => ['required', 'array', 'min:1', 'max:30'],
            'columns.*.id' => ['nullable', 'string', 'max:36'],
            'columns.*.key' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'columns.*.name' => ['required', 'string', 'max:128'],
            'rows' => ['nullable', 'array', 'max:500'],
            'rows.*.id' => ['nullable', 'string', 'max:36'],
            'rows.*.cells' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $columns = [];
        $columnKeys = [];

        foreach (array_values($payload['columns']) as $index => $column) {
            $key = (string) $column['key'];

            if (in_array($key, $columnKeys, true)) {
                throw ValidationException::withMessages([
                    "columns.{$index}.key" => 'Column keys must be unique.',
                ]);
            }

            $columnKeys[] = $key;
            $columns[] = [
                'id' => $this->resolveId($column['id'] ?? null),
                'key' => $key,
                'name' => (string) $column['name'],
            ];
        }

        $rows = [];

        foreach (array_values($payload['rows'] ?? []) as $index => $row) {
            $cells = $row['cells'] ?? [];

            if (!is_array($cells)) {
                throw ValidationException::withMessages([
                    "rows.{$index}.cells" => 'Cells must be an object of column keys.',
                ]);
            }

            $normalizedCells = [];

            foreach ($cells as $cellKey => $cellValue) {
                if (!in_array((string) $cellKey, $columnKeys, true)) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.cells.{$cellKey}" => 'Unknown column key.',
                    ]);
                }

                if (!$this->isAllowedCellValue($cellValue)) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.cells.{$cellKey}" => 'Cell value must be a string, number, boolean or null.',
                    ]);
                }

                $normalizedCells[(string) $cellKey] = $cellValue;
            }

            $rows[] = [
                'id' => $this->resolveId($row['id'] ?? null),
                'cells' => $normalizedCells,
            ];
        }

        return [
            'title' => isset($payload['title']) && $payload['title'] !== ''
                ? (string) $payload['title']
                : null,
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function resolveId(mixed $id): string
    {
        if (is_string($id) && $id !== '') {
            return $id;
        }

        return (string) Str::uuid();
    }

    private function isAllowedCellValue(mixed $value): bool
    {
        return $value === null
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value);
    }
}

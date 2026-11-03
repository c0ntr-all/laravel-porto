<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->exists('progress') || !is_string($this->input('progress'))) {
            return;
        }

        $this->merge([
            'progress' => str_replace(',', '.', trim($this->input('progress'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'track_id' => 'required|integer|exists:music_tracks,id',
            'progress' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (!$this->hasAtMostTwoDecimalPlaces($value)) {
                        $fail('Поле progress должно быть числом от 0 до 100 с не более чем двумя знаками после запятой.');
                    }
                },
            ],
        ];
    }

    private function hasAtMostTwoDecimalPlaces(mixed $value): bool
    {
        if (is_string($value)) {
            return preg_match('/^\d+(\.\d{1,2})?$/', str_replace(',', '.', trim($value))) === 1;
        }

        if (is_int($value)) {
            return $value >= 0;
        }

        if (!is_float($value)) {
            return false;
        }

        $scaled = round($value * 100, 8);

        return abs($scaled - round($scaled)) < 1e-6;
    }
}

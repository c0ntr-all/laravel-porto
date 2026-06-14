<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ChecklistItemUpdateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:70',
            'is_finished' => [
                'sometimes',
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($this->input('is_declined') && !($value xor $this->input('is_declined'))) {
                        $fail('Нельзя одновременно завершать и отклонять чеклист');
                    }
                }
            ],
            'is_declined' => [
                'sometimes',
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value === true && empty($this->input('decline_reason'))) {
                        $fail('При отказе необходимо указать причину');
                    }
                }
            ],
            'decline_reason' => [
                'string',
                'min:5',
                'max:500'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'decline_reason.min' => 'Причина отказа должна содержать минимум 5 символов',
            'decline_reason.max' => 'Причина отказа не должна превышать 500 символов'
        ];
    }
}

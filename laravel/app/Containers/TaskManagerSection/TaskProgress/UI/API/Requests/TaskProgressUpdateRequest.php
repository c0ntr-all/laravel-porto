<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class TaskProgressUpdateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:100',
            'content' => 'sometimes|string|max:3000',
            'is_final' => 'sometimes|bool',
        ];
    }
}

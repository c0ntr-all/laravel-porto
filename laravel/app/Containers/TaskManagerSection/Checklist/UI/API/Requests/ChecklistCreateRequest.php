<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ChecklistCreateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100'
        ];
    }
}

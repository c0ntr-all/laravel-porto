<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:tags|max:50',
            'content' => 'sometimes|string|max:30000'
        ];
    }
}

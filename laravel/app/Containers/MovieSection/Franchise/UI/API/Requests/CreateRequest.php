<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('movie_franchises', 'name')->whereNull('deleted_at'),
            ],
            'description' => 'sometimes|nullable|string',
            'order' => 'sometimes|integer|min:0',
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class ListFolderMoviesRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort' => [
                'sometimes',
                'string',
                Rule::in([
                    'added_at',
                    '-added_at',
                    'title',
                    '-title',
                    'year',
                    '-year',
                    'kp_rating',
                    '-kp_rating',
                ]),
            ],
        ];
    }
}

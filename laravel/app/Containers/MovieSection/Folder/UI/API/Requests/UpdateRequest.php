<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $folder = $this->route('folder');

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('movie_folders', 'name')
                    ->where('user_id', $this->user()?->id)
                    ->ignore($folder),
            ],
        ];
    }
}

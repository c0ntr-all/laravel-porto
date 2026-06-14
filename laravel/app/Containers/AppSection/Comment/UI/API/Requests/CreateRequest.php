<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Requests;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'commentable_id' => 'required|numeric',
            'commentable_type' => ['required', Rule::in(ContainerAliasEnum::toArray())],
            'content' => 'required|string|max:1000'
        ];
    }
}

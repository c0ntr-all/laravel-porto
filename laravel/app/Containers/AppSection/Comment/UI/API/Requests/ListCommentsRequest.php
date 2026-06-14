<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListCommentsRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}

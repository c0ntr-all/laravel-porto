<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class RateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'rate' => 'required|integer|min:0|max:4'
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ImportSeasonsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'kp_id' => 'required|integer|min:1|exists:movies,kp_id',
        ];
    }

    public function messages(): array
    {
        return [
            'kp_id.exists' => 'Movie with this Kinopoisk id was not found. Import the series first.',
        ];
    }
}

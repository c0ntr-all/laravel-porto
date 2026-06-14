<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListPeriodsRequest extends AuthenticatedRequest
{

    public function rules(): array
    {
        return [
            //
        ];
    }
}

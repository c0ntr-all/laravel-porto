<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class DeleteRequest extends AdminRequest
{
    public function rules(): array
    {
        return [];
    }
}

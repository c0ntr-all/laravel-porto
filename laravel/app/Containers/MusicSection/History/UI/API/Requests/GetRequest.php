<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class GetRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize()
            && $this->route('history')?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [];
    }
}

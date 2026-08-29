<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class TagGroupDeleteRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        $group = $this->route('tagGroup');

        return parent::authorize() && $group && !$group->is_system;
    }

    public function rules(): array
    {
        return [];
    }
}

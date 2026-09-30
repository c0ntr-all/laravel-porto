<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class SyncVideoTagsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $userId = (int) $this->user()?->id;

        return [
            'tags' => 'required_without:new_tags|array',
            'tags.*' => [
                'integer',
                Rule::exists('tags', 'id')->where('user_id', $userId),
            ],
            'new_tags' => 'required_without:tags|array',
            'new_tags.*' => 'string|max:50',
        ];
    }
}

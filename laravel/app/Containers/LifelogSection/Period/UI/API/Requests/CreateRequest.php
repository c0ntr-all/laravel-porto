<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'start_post_id' => [
                'required',
                'string',
                'numeric',
                Rule::exists('lifelog_posts', 'id')->where('user_id', auth()->id()),
            ],
            'end_post_id' => [
                'required',
                'string',
                'numeric',
                Rule::exists('lifelog_posts', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }
}

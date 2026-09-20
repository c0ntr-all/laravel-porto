<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $isMovieContent = $this->isMovieContentType();

        return [
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'content_type' => ['sometimes', Rule::enum(PostContentTypeEnum::class)],
            'date' => 'required|date_format:Y-m-d',
            'time' => 'sometimes|date_format:H:i|nullable',
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|string',
            'new_tags' => 'sometimes|array',
            'new_tags.*' => 'sometimes|string|max:20',
            'attachments' => 'sometimes|array',
            'attachments.*.type' => [
                'required',
                Rule::in(ContainerAliasEnum::attachmentFileableTypes()),
            ],
            'attachments.*.id' => 'required|string|max:36',
            'movie_id' => [
                Rule::requiredIf(fn () => $isMovieContent && !$this->filled('movie_title')),
                Rule::prohibitedIf(fn () => !$isMovieContent),
                'nullable',
                'integer',
                'exists:movies,id',
                'prohibits:movie_title',
            ],
            'movie_title' => [
                Rule::requiredIf(fn () => $isMovieContent && !$this->filled('movie_id')),
                Rule::prohibitedIf(fn () => !$isMovieContent),
                'nullable',
                'string',
                'max:255',
                'prohibits:movie_id',
            ],
        ];
    }

    private function isMovieContentType(): bool
    {
        $contentType = $this->input('content_type', PostContentTypeEnum::DEFAULT->value);

        return in_array($contentType, [
            PostContentTypeEnum::MOVIE->value,
            PostContentTypeEnum::TV_SERIES->value,
        ], true);
    }
}

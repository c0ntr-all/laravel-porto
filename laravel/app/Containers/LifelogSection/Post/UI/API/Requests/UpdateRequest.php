<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $requiresMoviePayload = $this->requiresMoviePayload();
        $allowsWatch = $this->allowsWatch();

        return [
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'content_type' => ['sometimes', Rule::enum(PostContentTypeEnum::class)],
            'date' => 'sometimes|date_format:Y-m-d',
            'time' => 'sometimes|date_format:H:i|nullable',
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|string',
            'new_tags' => 'sometimes|array',
            'new_tags.*' => 'sometimes|string|unique:App\Containers\AppSection\Tag\Models\Tag,name',
            'deleted_attachments_ids' => 'sometimes|array',
            'deleted_attachments_ids.*' => 'sometimes|string|max:36',
            'attachments' => 'sometimes|array',
            'attachments.*.type' => [
                'required',
                Rule::in(ContainerAliasEnum::attachmentFileableTypes()),
            ],
            'attachments.*.id' => 'required|string|max:36',
            'movie_id' => [
                Rule::requiredIf(fn () => $requiresMoviePayload && !$this->filled('movie_title')),
                Rule::prohibitedIf(fn () => $this->isNonMovieContentType()),
                'sometimes',
                'nullable',
                'integer',
                'exists:movies,id',
                'prohibits:movie_title',
            ],
            'movie_title' => [
                Rule::requiredIf(fn () => $requiresMoviePayload && !$this->filled('movie_id')),
                Rule::prohibitedIf(fn () => $this->isNonMovieContentType()),
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'prohibits:movie_id',
            ],
            'watch' => [
                Rule::prohibitedIf(fn () => !$allowsWatch),
                'sometimes',
                'nullable',
                'array',
            ],
            'watch.season' => [
                Rule::requiredIf(fn () => $this->filled('watch')),
                'integer',
                'min:1',
            ],
            'watch.episode_from' => [
                Rule::requiredIf(fn () => $this->filled('watch')),
                'integer',
                'min:1',
            ],
            'watch.episode_to' => [
                Rule::requiredIf(fn () => $this->filled('watch')),
                'integer',
                'min:1',
                'gte:watch.episode_from',
            ],
            'watch.stopped_at' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
            ],
        ];
    }

    private function requiresMoviePayload(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === null) {
            return false;
        }

        return in_array($contentType, [
            PostContentTypeEnum::MOVIE->value,
            PostContentTypeEnum::TV_SERIES->value,
        ], true);
    }

    private function isNonMovieContentType(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === null) {
            return false;
        }

        return !in_array($contentType, [
            PostContentTypeEnum::MOVIE->value,
            PostContentTypeEnum::TV_SERIES->value,
        ], true);
    }

    private function allowsWatch(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === PostContentTypeEnum::TV_SERIES->value) {
            return true;
        }

        if ($contentType !== null) {
            return false;
        }

        /** @var Post|null $post */
        $post = $this->route('post');

        return $post instanceof Post
            && $post->content_type === PostContentTypeEnum::TV_SERIES;
    }
}

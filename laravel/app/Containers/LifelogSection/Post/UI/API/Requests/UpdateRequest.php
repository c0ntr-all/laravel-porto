<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\UI\API\Rules\AttachableAlbumRule;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $allowsStartedAt = $this->allowsStartedAt();
        $requiresTaskPayload = $this->requiresTaskPayload();
        $requiresAlbumPayload = $this->requiresAlbumPayload();
        $requiresNotePayload = $this->requiresNotePayload();
        $userId = auth()->id();

        return [
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'content_type' => ['sometimes', Rule::enum(PostContentTypeEnum::class)],
            'is_draft' => 'sometimes|boolean',
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
            'started_at' => [
                Rule::prohibitedIf(fn () => !$allowsStartedAt),
                'sometimes',
                'nullable',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2}( \d{1,2}:\d{2})?$/',
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
            'task_id' => [
                Rule::requiredIf(fn () => $requiresTaskPayload),
                Rule::prohibitedIf(fn () => $this->isNonTaskContentType()),
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('tm_tasks', 'id')->where('user_id', $userId),
            ],
            'album_id' => [
                Rule::requiredIf(fn () => $requiresAlbumPayload),
                Rule::prohibitedIf(fn () => $this->isNonAlbumContentType()),
                'sometimes',
                'nullable',
                'integer',
                new AttachableAlbumRule((int) $userId),
            ],
            'note_id' => [
                Rule::requiredIf(fn () => $requiresNotePayload),
                Rule::prohibitedIf(fn () => $this->isNonNoteContentType()),
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('notes', 'id')->where('user_id', $userId)->whereNull('deleted_at'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startedAt = $this->input('started_at');
            if (!is_string($startedAt) || trim($startedAt) === '') {
                return;
            }

            /** @var Post|null $post */
            $post = $this->route('post');
            $endDate = $this->input('date', $post instanceof Post ? $post->date?->format('Y-m-d') : null);
            if (!is_string($endDate) || $endDate === '') {
                return;
            }

            $endTime = $this->exists('time')
                ? $this->input('time')
                : ($post instanceof Post ? $post->time?->format('H:i') : null);

            $end = is_string($endTime) && $endTime !== ''
                ? "{$endDate} {$endTime}"
                : $endDate;

            $start = trim($startedAt);

            if (strtotime($start) > strtotime($end)) {
                $validator->errors()->add(
                    'started_at',
                    'Дата начала не может быть позже окончания.'
                );
            }
        });
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

    private function requiresTaskPayload(): bool
    {
        return $this->input('content_type') === PostContentTypeEnum::TASK->value;
    }

    private function isNonTaskContentType(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === null) {
            return false;
        }

        return $contentType !== PostContentTypeEnum::TASK->value;
    }

    private function requiresAlbumPayload(): bool
    {
        return $this->input('content_type') === PostContentTypeEnum::ALBUM->value;
    }

    private function isNonAlbumContentType(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === null) {
            return false;
        }

        return $contentType !== PostContentTypeEnum::ALBUM->value;
    }

    private function requiresNotePayload(): bool
    {
        return $this->input('content_type') === PostContentTypeEnum::NOTE->value;
    }

    private function isNonNoteContentType(): bool
    {
        $contentType = $this->input('content_type');

        if ($contentType === null) {
            return false;
        }

        return $contentType !== PostContentTypeEnum::NOTE->value;
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

    private function allowsStartedAt(): bool
    {
        $contentType = $this->input('content_type');

        if (in_array($contentType, [
            PostContentTypeEnum::MOVIE->value,
            PostContentTypeEnum::TV_SERIES->value,
        ], true)) {
            return true;
        }

        if ($contentType !== null) {
            return false;
        }

        /** @var Post|null $post */
        $post = $this->route('post');

        return $post instanceof Post
            && in_array($post->content_type, [
                PostContentTypeEnum::MOVIE,
                PostContentTypeEnum::TV_SERIES,
            ], true);
    }
}

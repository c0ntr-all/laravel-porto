<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\UI\API\Requests;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => 'sometimes|string|max:1000',
            'icon' => 'sometimes|string|max:50',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
            'date_from' => 'sometimes|nullable|date_format:Y-m-d H:i',
            'date_to' => 'sometimes|nullable|date_format:Y-m-d H:i|after_or_equal:date_from',
            'text' => 'sometimes|nullable|string|max:255',
            'content_type' => ['sometimes', 'nullable', $this->contentTypeRule()],
            'content_type.*' => ['string', Rule::enum(PostContentTypeEnum::class)],
        ];
    }

    private function contentTypeRule(): mixed
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (is_string($value)) {
                if (PostContentTypeEnum::tryFrom($value) === null) {
                    $fail('The selected content type is invalid.');
                }

                return;
            }

            if (!is_array($value)) {
                $fail('The content type must be a string or an array of strings.');
            }
        };
    }
}

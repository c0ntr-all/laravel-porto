<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use App\Ship\Parents\Requests\AdminRequest;

class UpdateRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeTags();
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'integer|exists:music_tags,id',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }

    private function normalizeTags(): void
    {
        if (!$this->exists('tags')) {
            return;
        }

        $tags = $this->input('tags');

        if (!is_array($tags)) {
            $this->merge(['tags' => []]);

            return;
        }

        $this->merge([
            'tags' => array_values(array_filter(
                $tags,
                static fn (mixed $id): bool => $id !== null && $id !== '',
            )),
        ]);
    }
}

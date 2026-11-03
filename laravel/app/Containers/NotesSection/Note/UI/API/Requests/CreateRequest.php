<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\API\Requests;

use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->exists('name') && is_string($this->input('name'))) {
            $this->merge([
                'name' => trim($this->input('name')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:'.Note::NAME_MAX_LENGTH,
            'content' => 'sometimes|nullable|string|max:'.Note::CONTENT_MAX_LENGTH,
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('notes', 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()?->id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }
}

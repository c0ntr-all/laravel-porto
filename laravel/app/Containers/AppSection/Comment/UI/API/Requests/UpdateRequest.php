<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->exists('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => trim($this->input('content')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:1000',
        ];
    }
}

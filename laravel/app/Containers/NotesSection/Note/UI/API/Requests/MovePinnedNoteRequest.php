<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class MovePinnedNoteRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'before_id' => 'present|nullable|integer',
        ];
    }
}

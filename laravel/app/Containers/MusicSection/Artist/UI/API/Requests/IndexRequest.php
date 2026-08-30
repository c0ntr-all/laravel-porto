<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Ship\Parents\Requests\AuthenticatedRequest;

class IndexRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:'.ArtistRepository::MAX_PER_PAGE,
            'cursor' => 'sometimes|nullable|string',
        ];
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? ArtistRepository::DEFAULT_PER_PAGE);
    }
}

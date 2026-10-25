<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Rules;

use App\Containers\GallerySection\Album\Models\Album;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AttachableAlbumRule implements ValidationRule
{
    public function __construct(
        private readonly int $userId,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $album = Album::withoutGlobalScopes()->find($value);

        if (!$album) {
            $fail('Альбом не найден.');

            return;
        }

        if ($album->isSystem() || $album->user_id === null) {
            $fail('Системные альбомы нельзя прикреплять к посту.');

            return;
        }

        if ((int) $album->user_id !== $this->userId) {
            $fail('Альбом не найден или недоступен.');
        }
    }
}

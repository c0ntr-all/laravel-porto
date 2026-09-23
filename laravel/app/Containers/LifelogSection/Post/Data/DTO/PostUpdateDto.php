<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PostUpdateDto extends Data
{
    public int $user_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    public PostContentTypeEnum|Optional $content_type;
    public string|Optional $date;
    public string|Optional|null $time;

    public function __construct(
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function attributesForUpdate(): array
    {
        $attributes = [];

        foreach ($this->toArray() as $key => $value) {
            if ($value instanceof Optional || $key === 'user_id') {
                continue;
            }

            $attributes[$key] = $value;
        }

        return $attributes;
    }
}

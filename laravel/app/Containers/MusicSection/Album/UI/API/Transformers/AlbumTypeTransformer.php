<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Transformers;

use App\Containers\MusicSection\Album\Models\AlbumType;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class AlbumTypeTransformer extends TransformerAbstract
{
    public function transform(AlbumType $type): array
    {
        return self::payload($type) ?? [];
    }

    /**
     * @return array{id: string, name: string, slug: string, label: string}|null
     */
    public static function payload(?AlbumType $type): ?array
    {
        if ($type === null) {
            return null;
        }

        return [
            'id' => (string) $type->id,
            'name' => $type->name,
            'slug' => $type->slug,
            'label' => Str::of($type->name)->replace('-', ' ')->title()->toString(),
        ];
    }
}

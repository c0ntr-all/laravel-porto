<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\API\Transformers;

use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Containers\AppSection\Document\Models\Document;
use App\Containers\AppSection\Document\UI\API\Transformers\DocumentTransformer;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Database\Eloquent\Relations\Relation;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment): array
    {
        $fileableType = ContainerAliasEnum::toCanonicalMorphAlias((string) $attachment->fileable_type);
        $fileable = $attachment->fileable;
        $fileableClass = Relation::getMorphedModel((string) $attachment->fileable_type);

        $data = match ($fileableClass) {
            Image::class => app(ImageTransformer::class)->transform($fileable),
            Video::class => app(VideoTransformer::class)->transform($fileable),
            Document::class => app(DocumentTransformer::class)->transform($fileable),
            default => throw new \RuntimeException('Unknown attachment type: ' . $attachment->fileable_type),
        };

        return [
            'attachment_id' => (string) $attachment->id,
            'attachment_uuid' => (string) $attachment->uuid,
            'attachment_type' => $fileableType,
            'attachment_created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
            ...$data,
        ];
    }
}

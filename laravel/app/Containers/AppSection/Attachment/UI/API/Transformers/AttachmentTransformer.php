<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\API\Transformers;

use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment): array
    {
        $fileableType = $attachment->fileable_type;
        $fileable = $attachment->fileable;

        $data = match($fileableType) {
            ContainerAliasEnum::GALLERY_IMAGE->value => app(ImageTransformer::class)->transform($fileable),
            ContainerAliasEnum::GALLERY_VIDEO->value => app(VideoTransformer::class)->transform($fileable),
            default => throw new \RuntimeException('Unknown attachment type: ' . $fileableType),
        };

        // TODO: В будущем надо сделать как и полагается в json api: attachment - Один слой, а Image, Video - included
        return [
            'attachment_type' => $fileableType,
            'attachment_created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
            ...$data
        ];
    }
}

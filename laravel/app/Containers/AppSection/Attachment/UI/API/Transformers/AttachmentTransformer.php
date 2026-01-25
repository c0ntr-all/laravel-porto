<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\API\Transformers;

use App\Containers\AppSection\Attachment\Models\Attachment;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment): array
    {
        //TODO: Пока что тут жестко Image, но в будущем может быть и другое. Надо будет сделать маппинг
        $file = $attachment->fileable;
        $output = [
            'id' => $attachment->id,
            'attachment_type' => $attachment->fileable_type,
            'attachment_created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
            'source' => $file->source,
            'original_path' => $file->base_path,
            'list_thumb_path' => $file->list_thumb_path,
            'width' => $file->width,
            'height' => $file->height,
        ];

        if ($file->preview_thumb_path) {
            $output['preview_thumb_path'] = $file->preview_thumb_path;
        }

        return $output;
    }
}

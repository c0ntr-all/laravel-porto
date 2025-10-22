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
        return [
            'id' => $attachment->id,
            'attachment_type' => $attachment->fileable_type,
            'attachment_created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
            'source' => $file->source,
            'original_path' => $file->full_path,
            'list_thumb_path' => url('') . '/storage/' . $file->list_thumb_path,
            'preview_thumb_path' => url('') . '/storage/' . $file->preview_thumb_path,
            'width' => $file->width,
            'height' => $file->height,
        ];
    }
}

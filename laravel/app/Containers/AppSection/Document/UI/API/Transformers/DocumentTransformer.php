<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\UI\API\Transformers;

use App\Containers\AppSection\Document\Models\Document;
use League\Fractal\TransformerAbstract;

class DocumentTransformer extends TransformerAbstract
{
    public function transform(Document $document): array
    {
        return [
            'id' => (string) $document->id,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'extension' => $document->extension,
            'size' => $document->size,
            'download_url' => $document->download_url,
            'created_at' => $document->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $document->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Transformers;

use App\Containers\MusicSection\Upload\Models\MusicUploadTrack;
use League\Fractal\TransformerAbstract;

class UploadTrackTransformer extends TransformerAbstract
{
    public function transform(MusicUploadTrack $item): array
    {
        return [
            'id' => $item->id,
            'upload_id' => $item->upload_id,
            'track_id' => $item->track_id,
            'album_name' => $item->album_name,
            'track_name' => $item->track_name,
            'source_path' => $item->source_path,
            'status' => $item->status->value,
            'snapshot' => $item->snapshot,
            'error_message' => $item->error_message,
            'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

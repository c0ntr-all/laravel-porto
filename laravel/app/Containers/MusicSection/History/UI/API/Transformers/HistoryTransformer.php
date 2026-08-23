<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Transformers;

use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class HistoryTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'track',
    ];

    protected array $defaultIncludes = [
        'track',
    ];

    public function transform(History $history): array
    {
        return [
            'id' => $history->id,
            'track_id' => $history->track_id,
            'user_id' => $history->user_id,
            'created_at' => $history->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $history->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTrack(History $history): Item|NullResource
    {
        if (!$history->track) {
            return $this->null();
        }

        return $this->item($history->track, new TrackTransformer(), 'tracks');
    }
}

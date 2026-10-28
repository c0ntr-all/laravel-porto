<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\UI\API\Transformers;

use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Containers\GameSection\Import\Models\GameImport;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class GameImportTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'game',
    ];

    public function transform(GameImport $import): array
    {
        return [
            'id' => $import->id,
            'steam_id' => $import->steam_id,
            'game_id' => $import->game_id,
            'source_url' => $import->source_url,
            'status' => $import->status->value,
            'http_status' => $import->http_status,
            'was_created' => $import->was_created,
            'parsed_payload' => $import->parsed_payload,
            'meta' => $import->meta,
            'error_message' => $import->error_message,
            'started_at' => $import->started_at?->format('Y-m-d H:i:s'),
            'finished_at' => $import->finished_at?->format('Y-m-d H:i:s'),
            'duration_ms' => $import->duration_ms,
            'created_at' => $import->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeGame(GameImport $import): Item|NullResource
    {
        if ($import->game === null) {
            return $this->null();
        }

        return $this->item($import->game, new GameTransformer, ContainerAliasEnum::GAME->value);
    }
}

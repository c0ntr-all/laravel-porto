<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\API\Transformers;

use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class MovieImportTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'movie',
    ];

    public function transform(MovieImport $import): array
    {
        return [
            'id' => $import->id,
            'kp_id' => $import->kp_id,
            'movie_id' => $import->movie_id,
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

    public function includeMovie(MovieImport $import): Item|NullResource
    {
        if ($import->movie === null) {
            return $this->null();
        }

        return $this->item($import->movie, new MovieTransformer(), ContainerAliasEnum::MOVIE->value);
    }
}

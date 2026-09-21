<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\API\Transformers;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use Illuminate\Support\Carbon;

class FolderMovieTransformer extends MovieTransformer
{
    public function transform(Movie $movie): array
    {
        $data = parent::transform($movie);
        $addedAt = $movie->folder_added_at ?? $movie->pivot->added_at ?? null;

        $data['added_at'] = $addedAt
            ? Carbon::parse($addedAt)->format('Y-m-d H:i:s')
            : null;

        return $data;
    }
}

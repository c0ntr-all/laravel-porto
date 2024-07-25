<?php declare(strict_types=1);

namespace App\Containers\Music\UI\Actions\Artists;

use App\Containers\Music\Models\Artist;
use App\Containers\Music\UI\API\Resources\Artists\Page\ArtistResource;
use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class GetArtistAction
{
    use AsAction;

    public function handle(Artist $artist): Artist
    {
        return $artist->load(['albums', 'tags']);
    }

    public function asController(Artist $artist): Response
    {
        $data = $this->handle($artist);

        return response(new ArtistResource($data));
    }
}

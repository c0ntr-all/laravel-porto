<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Upload\UI\API\Transformers\UploadTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetUploadAction extends BaseAction
{
    public function handle(MusicUpload $upload): MusicUpload
    {
        return $upload->load(['artist', 'tracks']);
    }

    public function asController(MusicUpload $upload, GetRequest $request): JsonResponse
    {
        return fractal($this->handle($upload), new UploadTransformer())
            ->withResourceName('uploads')
            ->parseIncludes(['artist', 'tracks'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

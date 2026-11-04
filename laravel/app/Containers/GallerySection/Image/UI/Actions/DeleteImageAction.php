<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\DeleteImageTask;
use App\Containers\GallerySection\Image\UI\API\Requests\DeleteImageRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteImageAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly DeleteImageTask $deleteImageTask,
    ) {
        parent::__construct();
    }

    public function handle(Image $image, ?int $albumId = null): string
    {
        return DB::transaction(function () use ($image, $albumId) {
            if ($albumId !== null) {
                $belongs = $image->albums()->where('gallery_albums.id', $albumId)->exists()
                    || (int) $image->album_id === $albumId;

                if (!$belongs) {
                    abort(404);
                }

                $image->albums()->detach($albumId);

                if ($image->albums()->exists()) {
                    return 'detached';
                }
            }

            $this->recordUseCase($image);
            $this->deleteImageTask->run($image);

            return 'deleted';
        });
    }

    public function asController(Image $image, DeleteImageRequest $request): JsonResponse
    {
        $removed = $this->handle($image, $request->albumId());

        return response()->json([
            'meta' => [
                'message' => $removed === 'detached'
                    ? 'Image removed from the album.'
                    : 'Image successfully deleted!',
                'removed' => $removed,
            ],
        ]);
    }
}

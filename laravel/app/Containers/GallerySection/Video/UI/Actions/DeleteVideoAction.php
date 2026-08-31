<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\DeleteVideoTask;
use App\Containers\GallerySection\Video\UI\API\Requests\DeleteVideoRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class DeleteVideoAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly DeleteVideoTask $deleteVideoTask,
    ) {
        parent::__construct();
    }

    public function handle(Video $video): bool
    {
        return $this->deleteVideoTask->run($video);
    }

    public function asController(Video $video, DeleteVideoRequest $request): JsonResponse
    {
        $this->handle($video);

        return response()->json([
            'meta' => [
                'message' => 'Video successfully deleted!',
            ],
        ]);
    }
}

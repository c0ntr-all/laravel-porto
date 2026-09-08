<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\DTO\UploadVideoFromWebDto;
use App\Containers\GallerySection\Video\Factories\VideoSourceFactory;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\PersistVideoFromSourceTask;
use App\Containers\GallerySection\Video\UI\API\Requests\UploadVideoFromWebRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UploadVideoFromWebAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::WEB->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly PersistVideoFromSourceTask $persistVideoFromSourceTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadVideoFromWebDto $dto): Video
    {
        return DB::transaction(function () use ($album, $dto) {
            $source = VideoSourceFactory::create($dto->link, self::SOURCE_TYPE);
            $video = $this->persistVideoFromSourceTask->run(
                $album,
                (int) $dto->user_id,
                self::SOURCE_TYPE,
                $source,
            );

            if (!$album->isUploadStagingAlbum()) {
                $this->recordUseCase($video);
            }

            return $video;
        });
    }

    public function asController(Album $album, UploadVideoFromWebRequest $request): JsonResponse
    {
        $dto = UploadVideoFromWebDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $video = $this->handle($album, $dto);

        return fractal($video, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta(['message' => 'Video successfully uploaded!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Data\DTO\UpdateImageDto;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\UpdateImageTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UpdateImageRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateImageAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly UpdateImageTask $updateImageTask,
    ) {
        parent::__construct();
    }

    public function handle(Image $image, UpdateImageDto $dto): Image
    {
        return DB::transaction(function () use ($image, $dto) {
            $updated = $this->updateImageTask->run($image, $dto);

            $this->recordUseCase($updated);

            return $updated;
        });
    }

    public function asController(Image $image, UpdateImageRequest $request): JsonResponse
    {
        $dto = UpdateImageDto::from($request->validated());
        $image = $this->handle($image, $dto);

        return fractal($image, new ImageTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->addMeta(['message' => 'Image successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

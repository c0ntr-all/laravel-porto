<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseUpdateData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\UpdateFranchiseTask;
use App\Containers\MovieSection\Franchise\Tasks\UploadFranchiseImageTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly UpdateFranchiseTask $updateFranchiseTask,
        private readonly UploadFranchiseImageTask $uploadFranchiseImageTask,
    ) {
    }

    public function handle(
        Franchise $franchise,
        FranchiseUpdateData $dto,
        ?UploadedFile $imageFile = null,
    ): Franchise {
        return DB::transaction(function () use ($franchise, $dto, $imageFile) {
            if ($imageFile !== null) {
                $dto->image = $this->uploadFranchiseImageTask->run(
                    $imageFile,
                    (string) $franchise->id,
                );
            }

            return $this->updateFranchiseTask->run($franchise, $dto);
        });
    }

    public function asController(Franchise $franchise, UpdateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $imageFile = $payload['image_file'] ?? null;
        unset($payload['image_file']);

        $franchise = $this->handle(
            $franchise,
            FranchiseUpdateData::from($payload),
            $imageFile instanceof UploadedFile ? $imageFile : null,
        );

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Franchise updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseCreateData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\CreateFranchiseTask;
use App\Containers\MovieSection\Franchise\Tasks\UploadFranchiseImageTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly CreateFranchiseTask $createFranchiseTask,
        private readonly UploadFranchiseImageTask $uploadFranchiseImageTask,
    ) {
    }

    public function handle(FranchiseCreateData $dto, ?UploadedFile $imageFile = null): Franchise
    {
        return DB::transaction(function () use ($dto, $imageFile) {
            $franchise = $this->createFranchiseTask->run($dto);

            if ($imageFile !== null) {
                $franchise->image = $this->uploadFranchiseImageTask->run(
                    $imageFile,
                    (string) $franchise->id,
                );
                $franchise->save();
            }

            return $franchise->refresh();
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $imageFile = $payload['image_file'] ?? null;
        unset($payload['image_file']);

        $payload['user_id'] = (int) $request->user()->id;

        $franchise = $this->handle(
            FranchiseCreateData::from($payload),
            $imageFile instanceof UploadedFile ? $imageFile : null,
        );

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Franchise created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}

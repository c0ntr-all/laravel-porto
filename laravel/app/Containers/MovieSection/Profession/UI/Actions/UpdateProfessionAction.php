<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\Actions;

use App\Containers\MovieSection\Profession\Data\DTO\ProfessionUpdateData;
use App\Containers\MovieSection\Profession\Models\Profession;
use App\Containers\MovieSection\Profession\Tasks\UpdateProfessionTask;
use App\Containers\MovieSection\Profession\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Profession\UI\API\Transformers\ProfessionTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateProfessionAction extends BaseAction
{
    public function __construct(
        private readonly UpdateProfessionTask $updateProfessionTask,
    ) {
    }

    public function handle(Profession $profession, ProfessionUpdateData $dto): Profession
    {
        return $this->updateProfessionTask->run($profession, $dto);
    }

    public function asController(Profession $profession, UpdateRequest $request): JsonResponse
    {
        $profession = $this->handle($profession, ProfessionUpdateData::from($request->validated()));

        return fractal($profession, new ProfessionTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PROFESSION->value)
            ->addMeta(['message' => 'Profession updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}

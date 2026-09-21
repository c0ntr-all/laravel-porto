<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\Actions;

use App\Containers\MovieSection\Profession\Tasks\ListProfessionsTask;
use App\Containers\MovieSection\Profession\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Profession\UI\API\Transformers\ProfessionTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListProfessionsAction extends BaseAction
{
    public function __construct(
        private readonly ListProfessionsTask $listProfessionsTask,
    ) {
    }

    public function handle(): Collection
    {
        return $this->listProfessionsTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $professions = $this->handle();

        return fractal($professions, new ProfessionTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_PROFESSION->value)
            ->addMeta(['count' => $professions->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
